<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.landing')] class extends Component {
    // Single page wrapper using landing layout and rendering shared chat-widget component
};
?>

<div class="w-full">
    <livewire:chat-widget />
</div>
