<?php

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from knowledge base page', function () {
    $response = $this->get(route('admin.knowledge-base'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can render knowledge base page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.knowledge-base'));
    $response->assertOk();
});

test('file_name is required for upload', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('panduan.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', '')
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file_name' => 'required']);
});

test('valid docx document is renamed based on file_name and saved to private storage', function () {
    Storage::fake('local');
    Http::fake([
        '*' => Http::response(['success' => true, 'chunks' => 5], 200),
    ]);
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('random_input_name.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', 'panduan_pmb_2026')
        ->set('file', $file)
        ->set('deskripsi', 'Panduan Biaya SPP')
        ->call('uploadDocument')
        ->assertHasNoErrors();

    $doc = KnowledgeBase::where('metadata_name', 'panduan_pmb_2026.docx')->first();
    expect($doc)->not->toBeNull()
        ->and($doc->metadata_name)->toBe('panduan_pmb_2026.docx')
        ->and($doc->filename)->toBe('knowledge_bases/panduan_pmb_2026.docx')
        ->and($doc->status)->toBe('success')
        ->and($doc->user_id)->toBe($user->id)
        ->and($doc->user->id)->toBe($user->id)
        ->and($doc->deskripsi)->toBe('Panduan Biaya SPP');

    Storage::disk('local')->assertExists('knowledge_bases/panduan_pmb_2026.docx');
    Http::assertSent(fn ($request) => str_contains($request->url(), '/service/createnewknowledge'));
});

test('failed vectorization sets document status to failed', function () {
    Storage::fake('local');
    Http::fake([
        '*' => Http::response(['success' => false, 'detail' => 'Ollama offline'], 500),
    ]);
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('gagal.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', 'gagal_doc')
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasNoErrors();

    $doc = KnowledgeBase::where('metadata_name', 'gagal_doc.docx')->first();
    expect($doc)->not->toBeNull()
        ->and($doc->status)->toBe('failed');
});

test('reindexDocument updates status to success when ai service responds', function () {
    Storage::fake('local');
    Http::fake([
        '*' => Http::response(['success' => true, 'chunks' => 8], 200),
    ]);
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('retry.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $storedPath = $file->storeAs('knowledge_bases', 'retry.docx', 'local');

    $doc = KnowledgeBase::create([
        'filename' => $storedPath,
        'metadata_name' => 'retry.docx',
        'status' => 'failed',
    ]);

    Livewire::test('pages::admin.knowledge-base')
        ->call('reindexDocument', $doc->id);

    $doc->refresh();
    expect($doc->status)->toBe('success');
});

test('non docx document is rejected', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('panduan.pdf', 500, 'application/pdf');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', 'panduan_pdf')
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file' => 'mimes']);
});

test('document over 6mb is rejected', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    // 7MB = 7168KB > 6144KB limit
    $file = UploadedFile::fake()->create('large_file.docx', 7168, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', 'large_file')
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file' => 'max']);
});

test('duplicate metadata_name is rejected', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    KnowledgeBase::create([
        'filename' => 'knowledge_bases/existing.docx',
        'metadata_name' => 'duplicate_name.docx',
        'status' => 'success',
    ]);

    $file = UploadedFile::fake()->create('duplicate_name.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::test('pages::admin.knowledge-base')
        ->set('file_name', 'duplicate_name')
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file_name']);
});

test('authenticated user can download private document', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('biaya.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $storedPath = $file->storeAs('knowledge_bases', 'biaya.docx', 'local');

    $doc = KnowledgeBase::create([
        'filename' => $storedPath,
        'metadata_name' => 'biaya.docx',
        'status' => 'success',
    ]);

    Livewire::test('pages::admin.knowledge-base')
        ->call('downloadDocument', $doc->id)
        ->assertFileDownloaded('biaya.docx');
});

test('authenticated user can delete document and remove from private storage', function () {
    Storage::fake('local');
    Http::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('syarat.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $storedPath = $file->storeAs('knowledge_bases', 'syarat.docx', 'local');

    $doc = KnowledgeBase::create([
        'filename' => $storedPath,
        'metadata_name' => 'syarat.docx',
        'status' => 'success',
    ]);

    Storage::disk('local')->assertExists($storedPath);

    Livewire::test('pages::admin.knowledge-base')
        ->call('deleteDocument', $doc->id);

    expect(KnowledgeBase::find($doc->id))->toBeNull();
    Storage::disk('local')->assertMissing($storedPath);
});
