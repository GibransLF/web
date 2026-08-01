---
name: Academic Excellence System
colors:
    surface: "#f9f9f9"
    surface-dim: "#dadada"
    surface-bright: "#f9f9f9"
    surface-container-lowest: "#ffffff"
    surface-container-low: "#f3f3f3"
    surface-container: "#eeeeee"
    surface-container-high: "#e8e8e8"
    surface-container-highest: "#e2e2e2"
    on-surface: "#1a1c1c"
    on-surface-variant: "#454651"
    inverse-surface: "#2f3131"
    inverse-on-surface: "#f1f1f1"
    outline: "#767683"
    outline-variant: "#c6c5d3"
    surface-tint: "#4b57ac"
    primary: "#000e65"
    on-primary: "#ffffff"
    primary-container: "#1b287d"
    on-primary-container: "#8894ed"
    inverse-primary: "#bcc3ff"
    secondary: "#715c00"
    on-secondary: "#ffffff"
    secondary-container: "#ffd312"
    on-secondary-container: "#705b00"
    tertiary: "#1c1e1e"
    on-tertiary: "#ffffff"
    tertiary-container: "#313333"
    on-tertiary-container: "#9a9b9b"
    error: "#ba1a1a"
    on-error: "#ffffff"
    error-container: "#ffdad6"
    on-error-container: "#93000a"
    primary-fixed: "#dfe0ff"
    primary-fixed-dim: "#bcc3ff"
    on-primary-fixed: "#000d60"
    on-primary-fixed-variant: "#323e92"
    secondary-fixed: "#ffe17a"
    secondary-fixed-dim: "#ecc300"
    on-secondary-fixed: "#231b00"
    on-secondary-fixed-variant: "#554500"
    tertiary-fixed: "#e2e2e2"
    tertiary-fixed-dim: "#c6c6c7"
    on-tertiary-fixed: "#1a1c1c"
    on-tertiary-fixed-variant: "#454747"
    background: "#f9f9f9"
    on-background: "#1a1c1c"
    surface-variant: "#e2e2e2"
typography:
    headline-xl:
        fontFamily: Inter
        fontSize: 48px
        fontWeight: "700"
        lineHeight: 56px
        letterSpacing: -0.02em
    headline-xl-mobile:
        fontFamily: Inter
        fontSize: 32px
        fontWeight: "700"
        lineHeight: 40px
        letterSpacing: -0.02em
    headline-lg:
        fontFamily: Inter
        fontSize: 32px
        fontWeight: "600"
        lineHeight: 40px
        letterSpacing: -0.01em
    headline-md:
        fontFamily: Inter
        fontSize: 24px
        fontWeight: "600"
        lineHeight: 32px
    body-lg:
        fontFamily: Inter
        fontSize: 18px
        fontWeight: "400"
        lineHeight: 28px
    body-md:
        fontFamily: Inter
        fontSize: 16px
        fontWeight: "400"
        lineHeight: 24px
    label-md:
        fontFamily: Inter
        fontSize: 14px
        fontWeight: "500"
        lineHeight: 20px
        letterSpacing: 0.01em
    label-sm:
        fontFamily: Inter
        fontSize: 12px
        fontWeight: "600"
        lineHeight: 16px
        letterSpacing: 0.05em
rounded:
    sm: 0.125rem
    DEFAULT: 0.25rem
    md: 0.375rem
    lg: 0.5rem
    xl: 0.75rem
    full: 9999px
spacing:
    container-max: 1280px
    gutter: 24px
    margin-mobile: 16px
    margin-desktop: 48px
    stack-sm: 8px
    stack-md: 16px
    stack-lg: 32px
    sidebar-width: 360px
---

## Brand & Style

The design system is engineered for a University Student Admissions (PMB) platform, balancing academic prestige with modern technological efficiency. The target audience includes prospective students and parents who require a seamless, trustworthy, and high-performance enrollment experience.

The visual style follows a **Corporate / Modern** aesthetic with **Minimalist** influences. It prioritizes clarity, systematic organization, and a sense of institutional reliability. Design decisions are centered around "Tech-Integrated Academia"—merging traditional university values (trust, stability) with modern digital tools (AI assistance, rapid navigation). The interface uses generous whitespace to reduce cognitive load during the complex application process.

## Colors

The palette is anchored by **Deep Blue (#1B287D)**, symbolizing authority, intelligence, and stability. This is the primary driver for headers, primary buttons, and navigational elements. **Vibrant Yellow (#F9CE04)** serves as a high-visibility accent for Call-to-Action (CTA) elements, notifications, and interactive highlights, creating a energetic contrast that guides the user's eye toward registration.

The background uses **White Smoke (#F5F5F5)** to provide a soft, low-strain canvas that differentiates content sections from the pure white surface of cards and input fields. Text is rendered in a near-black neutral to ensure AAA accessibility standards against the light backgrounds.

## Typography

The design system utilizes **Inter** for all typographic roles. Its systematic, neutral, and utilitarian nature ensures maximum legibility across complex data-entry forms and informational brochures.

Headlines use tighter letter spacing and heavier weights to project a modern, authoritative voice. Body text maintains standard tracking for optimal readability in long-form admission requirements. Label styles are used for navigation and buttons, employing a medium weight to distinguish interactive elements from static content.

## Layout & Spacing

The layout employs a **Fluid Grid** system within a max-width container of 1280px. A 12-column structure governs the desktop view, while the mobile view collapses to a single-column stack.

**Key Layout Rules:**

- **Desktop:** Navbar is sticky. The AI Assistant sidebar is fixed to the right, occupying 360px, creating a split-screen feel when active.
- **Mobile:** The AI sidebar is hidden by default and accessible via a floating action trigger. The navbar transitions to a hamburger menu, adding a "Programs" or "Campus Life" link (total 6 items) to ensure comprehensive navigation in a constrained space.
- **Vertical Rhythm:** Content sections are separated by 80px to 120px of vertical padding to maintain a clean, institutional feel.

## Elevation & Depth

This design system uses **Tonal Layers** and **Low-contrast Outlines** to define hierarchy.

- **Level 0 (Background):** White Smoke (#F5F5F5).
- **Level 1 (Cards/Containers):** Pure White (#FFFFFF) with a subtle 1px border (#E5E7EB). Shadows are avoided for primary content to keep the look "flat" and modern.
- **Level 2 (AI Sidebar & Overlays):** Uses a soft ambient shadow (0px 4px 20px rgba(0,0,0,0.05)) to suggest it sits above the main content stream.
- **Glassmorphism:** Applied sparingly to the sticky Navbar using a backdrop blur (12px) and 90% opacity white fill to maintain context as the user scrolls.

## Shapes

The design system adopts a **Soft** shape language (4px - 8px radius). This provides a professional, geometric look that is more approachable than sharp corners but more serious than highly rounded "bubbly" designs.

- **Standard Buttons/Inputs:** 6px border radius.
- **Cards/Sidebar:** 8px border radius.
- **WhatsApp Floating Button:** Circular (Full rounded) to signify its unique status as a persistent utility.
- **Form Fields:** Crisp edges with subtle rounding to denote precision and technical integration.

## Components

### Buttons

- **Primary:** Deep Blue background, white text. No shadow, flat height.
- **Secondary:** Vibrant Yellow background, Deep Blue text. Used for "Register" to create immediate visual priority.
- **Ghost:** Transparent background, Deep Blue border/text. Used for secondary navigation links.

### Navbar

- Desktop: Logo (Left), 5 text links (Center), Login/Register buttons (Right).
- Mobile: Hamburger menu (Left/Right), Logo (Center). Expanded menu lists 6 links + CTA buttons.

### AI Assistant Sidebar

- Header: Integrated branding "AI Admissions Support."
- Interface: Message bubbles with White Smoke (AI) and Deep Blue (User) backgrounds.
- Footer: Input field with a "Send" icon and file attachment capability (Technical style).

### Footer

- High-density 4-column layout.
- Use `label-sm` for column headers in Deep Blue.
- Social icons use circular ghost button styles.

### Floating WhatsApp Button

- Positioned Bottom-Left (to avoid conflict with AI Assistant on the right).
- Vibrant Green (#25D366) with a white icon and a simple "Chat with us" tooltip.

### Input Fields

- Understated style: 1px light grey border that transitions to a 2px Deep Blue border on focus.
- Labels are persistent above the field using `label-md`.
