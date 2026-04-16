<div
    x-data="{
        baseTheme: $wire.entangle('data.base_theme'),
        primary: $wire.entangle('data.primary_color'),
        accent: $wire.entangle('data.accent_color'),
        roundness: $wire.entangle('data.roundness'),
        density: $wire.entangle('data.density'),
        mode: $wire.entangle('data.mode'),

        // Base theme definitions (from FlyonUI + custom bitfloo themes)
        themes: {
            'bitfloo-dark': {
                scheme: 'dark',
                base100: '#1a1b2e',
                base200: '#151624',
                base300: '#10111c',
                baseContent: '#e8e9ed',
                neutral: '#12131f',
                defaultPrimary: '#3b82f6',
                defaultAccent: '#10b981'
            },
            'bitfloo': {
                scheme: 'light',
                base100: '#ffffff',
                base200: '#f8f9fc',
                base300: '#eef0f5',
                baseContent: '#1e293b',
                neutral: '#0f172a',
                defaultPrimary: '#2563eb',
                defaultAccent: '#10b981'
            },
            'dark': {
                scheme: 'dark',
                base100: '#1d232a',
                base200: '#191e24',
                base300: '#15191e',
                baseContent: '#a6adba',
                neutral: '#2a323c',
                defaultPrimary: '#661ae6',
                defaultAccent: '#1fb2a5'
            },
            'light': {
                scheme: 'light',
                base100: '#ffffff',
                base200: '#f2f2f2',
                base300: '#e5e6e6',
                baseContent: '#1f2937',
                neutral: '#3d4451',
                defaultPrimary: '#570df8',
                defaultAccent: '#37cdbe'
            },
            'corporate': {
                scheme: 'light',
                base100: '#ffffff',
                base200: '#f8fafc',
                base300: '#e2e8f0',
                baseContent: '#181830',
                neutral: '#181830',
                defaultPrimary: '#4b6bfb',
                defaultAccent: '#7b92b2'
            },
            'luxury': {
                scheme: 'dark',
                base100: '#09090b',
                base200: '#171618',
                base300: '#2e2d2f',
                baseContent: '#dca54c',
                neutral: '#331800',
                defaultPrimary: '#ffffff',
                defaultAccent: '#dca54c'
            },
            'soft': {
                scheme: 'light',
                base100: '#faf7f5',
                base200: '#efeae6',
                base300: '#e7e2df',
                baseContent: '#261230',
                neutral: '#261230',
                defaultPrimary: '#d1c1d7',
                defaultAccent: '#f9d8e7'
            },
            'gourmet': {
                scheme: 'dark',
                base100: '#1c1917',
                base200: '#292524',
                base300: '#44403c',
                baseContent: '#d6d3d1',
                neutral: '#44403c',
                defaultPrimary: '#dc2626',
                defaultAccent: '#f59e0b'
            }
        },

        get theme() {
            return this.themes[this.baseTheme] || this.themes['bitfloo-dark'];
        },

        get isDark() {
            if (this.mode === 'auto') {
                return this.theme.scheme === 'dark';
            }
            return this.mode === 'dark';
        },

        // Use theme base colors, override with mode if explicitly set
        get base100() {
            if (this.mode !== 'auto' && this.mode !== this.theme.scheme) {
                return this.mode === 'dark' ? '#1a1b2e' : '#ffffff';
            }
            return this.theme.base100;
        },

        get base200() {
            if (this.mode !== 'auto' && this.mode !== this.theme.scheme) {
                return this.mode === 'dark' ? '#151624' : '#f8f9fc';
            }
            return this.theme.base200;
        },

        get baseContent() {
            if (this.mode !== 'auto' && this.mode !== this.theme.scheme) {
                return this.mode === 'dark' ? '#e8e9ed' : '#1e293b';
            }
            return this.theme.baseContent;
        },

        get mutedContent() {
            return this.isDark ? '#9ca3af' : '#6b7280';
        },

        get borderColor() {
            return this.isDark ? '#374151' : '#e5e7eb';
        },

        // Colors - use user's or theme default
        get primaryColor() {
            return this.primary || this.theme.defaultPrimary;
        },

        get accentColor() {
            return this.accent || this.theme.defaultAccent;
        },

        hexToRgb(hex) {
            if (!hex) return { r: 59, g: 130, b: 246 };
            hex = hex.replace('#', '');
            return {
                r: parseInt(hex.substr(0, 2), 16),
                g: parseInt(hex.substr(2, 2), 16),
                b: parseInt(hex.substr(4, 2), 16)
            };
        },

        luminance(hex) {
            const rgb = this.hexToRgb(hex);
            return (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255;
        },

        textOn(bgHex) {
            return this.luminance(bgHex) > 0.5 ? '#1f2937' : '#ffffff';
        },

        soften(hex, amount = 0.15) {
            const rgb = this.hexToRgb(hex);
            return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${amount})`;
        },

        // Style mappings
        get radiusBox() {
            return { 'sharp': '0', 'default': '0.5rem', 'rounded': '1rem', 'pill': '1.5rem' }[this.roundness] || '0.5rem';
        },

        get radiusBtn() {
            return { 'sharp': '0', 'default': '0.375rem', 'rounded': '0.75rem', 'pill': '9999px' }[this.roundness] || '0.375rem';
        },

        get radiusBadge() {
            return { 'sharp': '0.125rem', 'default': '9999px', 'rounded': '9999px', 'pill': '9999px' }[this.roundness] || '9999px';
        },

        get btnPadding() {
            return { 'compact': '0.5rem 1rem', 'comfortable': '0.625rem 1.25rem', 'spacious': '0.75rem 1.5rem' }[this.density] || '0.625rem 1.25rem';
        },

        get inputHeight() {
            return { 'compact': '2.25rem', 'comfortable': '2.75rem', 'spacious': '3.25rem' }[this.density] || '2.75rem';
        },

        get contrastOk() {
            return this.luminance(this.primaryColor) < 0.5;
        }
    }"
    class="space-y-4"
>
    {{-- Theme Name Badge --}}
    <div class="flex items-center justify-between">
        <span
            class="text-xs font-medium px-2 py-1 rounded"
            :style="{
                backgroundColor: soften(primaryColor),
                color: primaryColor
            }"
            x-text="baseTheme"
        ></span>
        <span
            class="text-xs px-2 py-0.5 rounded"
            :class="isDark ? 'bg-gray-700 text-gray-300' : 'bg-gray-200 text-gray-600'"
            x-text="isDark ? 'Dark' : 'Light'"
        ></span>
    </div>

    {{-- Preview Container --}}
    <div
        class="rounded-lg overflow-hidden border transition-colors duration-300"
        :style="{
            backgroundColor: base100,
            borderColor: borderColor,
            color: baseContent
        }"
    >
        <div class="p-4 space-y-5">

            {{-- Buttons --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider" :style="{ color: mutedContent }">Przyciski</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="font-medium text-sm transition-all shadow-sm"
                        :style="{
                            backgroundColor: primaryColor,
                            color: textOn(primaryColor),
                            borderRadius: radiusBtn,
                            padding: btnPadding
                        }"
                    >Primary</button>

                    <button
                        type="button"
                        class="font-medium text-sm transition-all shadow-sm"
                        :style="{
                            backgroundColor: accentColor,
                            color: textOn(accentColor),
                            borderRadius: radiusBtn,
                            padding: btnPadding
                        }"
                    >Accent</button>

                    <button
                        type="button"
                        class="font-medium text-sm border-2 bg-transparent"
                        :style="{
                            borderColor: primaryColor,
                            color: primaryColor,
                            borderRadius: radiusBtn,
                            padding: btnPadding
                        }"
                    >Outline</button>

                    <button
                        type="button"
                        class="font-medium text-sm bg-transparent"
                        :style="{
                            color: primaryColor,
                            borderRadius: radiusBtn,
                            padding: btnPadding
                        }"
                    >Ghost</button>
                </div>
            </div>

            {{-- Badges --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider" :style="{ color: mutedContent }">Badge</p>
                <div class="flex flex-wrap gap-2">
                    <span
                        class="text-xs font-medium px-2.5 py-1"
                        :style="{ backgroundColor: primaryColor, color: textOn(primaryColor), borderRadius: radiusBadge }"
                    >Primary</span>

                    <span
                        class="text-xs font-medium px-2.5 py-1"
                        :style="{ backgroundColor: soften(primaryColor), color: primaryColor, borderRadius: radiusBadge }"
                    >Soft</span>

                    <span
                        class="text-xs font-medium px-2.5 py-1"
                        :style="{ backgroundColor: accentColor, color: textOn(accentColor), borderRadius: radiusBadge }"
                    >Accent</span>

                    <span
                        class="text-xs font-medium px-2.5 py-1"
                        :style="{ backgroundColor: soften(accentColor), color: accentColor, borderRadius: radiusBadge }"
                    >Soft</span>
                </div>
            </div>

            {{-- Card --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider" :style="{ color: mutedContent }">Karta</p>
                <div
                    class="shadow-sm border"
                    :style="{
                        backgroundColor: base200,
                        borderColor: borderColor,
                        borderRadius: radiusBox
                    }"
                >
                    <div class="p-4">
                        <h4 class="font-semibold text-sm mb-1" :style="{ color: primaryColor }">
                            Tytuł karty
                        </h4>
                        <p class="text-xs mb-3" :style="{ color: mutedContent }">
                            Przykładowy opis zawartości karty z tekstem.
                        </p>
                        <a href="#" class="text-xs font-medium hover:underline" :style="{ color: accentColor }" @click.prevent>
                            Czytaj więcej →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider" :style="{ color: mutedContent }">Formularz</p>
                <div class="flex gap-2 items-stretch">
                    <input
                        type="text"
                        placeholder="Wpisz tekst..."
                        class="flex-1 px-3 text-sm border outline-none"
                        :style="{
                            backgroundColor: base200,
                            borderColor: borderColor,
                            color: baseContent,
                            borderRadius: radiusBtn,
                            height: inputHeight
                        }"
                    />
                    <button
                        type="button"
                        class="font-medium text-sm shadow-sm whitespace-nowrap"
                        :style="{
                            backgroundColor: primaryColor,
                            color: textOn(primaryColor),
                            borderRadius: radiusBtn,
                            padding: btnPadding,
                            height: inputHeight
                        }"
                    >Wyślij</button>
                </div>
            </div>

            {{-- Contrast --}}
            <div class="pt-3 border-t" :style="{ borderColor: borderColor }">
                <template x-if="contrastOk">
                    <div class="flex items-center gap-1.5 text-green-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-xs font-medium">Kontrast OK</span>
                    </div>
                </template>
                <template x-if="!contrastOk">
                    <div class="flex items-center gap-1.5 text-amber-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        <span class="text-xs font-medium">Niski kontrast</span>
                    </div>
                </template>
            </div>

        </div>
    </div>

    {{-- Color Swatches --}}
    <div class="grid grid-cols-2 gap-2">
        <div class="flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-gray-700">
            <div class="w-6 h-6 rounded shadow-inner" :style="{ backgroundColor: primaryColor }"></div>
            <div>
                <p class="text-xs font-medium text-gray-900 dark:text-white">Primary</p>
                <p class="text-[10px] text-gray-500" x-text="primaryColor"></p>
            </div>
        </div>
        <div class="flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-gray-700">
            <div class="w-6 h-6 rounded shadow-inner" :style="{ backgroundColor: accentColor }"></div>
            <div>
                <p class="text-xs font-medium text-gray-900 dark:text-white">Accent</p>
                <p class="text-[10px] text-gray-500" x-text="accentColor"></p>
            </div>
        </div>
    </div>
</div>
