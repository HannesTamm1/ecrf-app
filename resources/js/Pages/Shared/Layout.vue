<template>
    <div class="min-h-screen">
        <header class="bg-white shadow">
            <div class="mx-auto max-w-6xl px-4 py-4 flex gap-4">
                <h1 class="font-semibold">eCRF Metadata Generator & Importer</h1>
                <nav class="ml-auto flex gap-3">
                    <a class="text-blue-700 hover:underline" href="/">Upload</a>
                    <a class="text-blue-700 hover:underline" href="/wizard">Import Wizard</a>
                    <a class="text-blue-700 hover:underline" href="/output">Output Generator</a>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-4 py-6">
            <slot />
        </main>
    </div>
</template>
