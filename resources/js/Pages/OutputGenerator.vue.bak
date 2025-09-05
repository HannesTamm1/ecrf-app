<script setup>
import Layout from './Shared/Layout.vue'
import axios from 'axios'
import { ref } from 'vue'

const projectId = ref('')
const tab = ref('scheduler') // scheduler | form | conditional
const exporting = ref(false)

async function doExport() {
    if (!projectId.value) return
    exporting.value = true
    try {
        const url = `/api/generate/${tab.value}?project_id=${projectId.value}`
        // Navigate to URL to trigger download
        window.location.href = url
    } finally { exporting.value = false }
}
</script>

<template>
    <Layout>
        <div class="bg-white p-4 rounded-2xl shadow">
            <h2 class="font-semibold mb-4">Output Generator</h2>

            <div class="flex items-end gap-3">
                <div>
                    <label class="block text-sm font-medium">Project ID</label>
                    <input v-model="projectId" placeholder="e.g. 1" class="mt-1 border rounded px-2 py-1" />
                </div>

                <div>
                    <label class="block text-sm font-medium">Overview Type</label>
                    <select v-model="tab" class="mt-1 border rounded px-2 py-1">
                        <option value="scheduler">Scheduler-Based Overview</option>
                        <option value="form">Form-Based Overview</option>
                        <option value="conditional">Conditional Fields Overview</option>
                    </select>
                </div>

                <button class="px-4 py-2 rounded-xl bg-blue-600 text-white" :disabled="!projectId || exporting"
                    @click="doExport">
                    {{ exporting ? 'Generating…' : 'Export .xlsx' }}
                </button>
            </div>

            <p class="text-xs text-gray-600 mt-3">
                File naming follows URS conventions. Ensure you’ve uploaded a Project JSON first to get a Project ID.
            </p>
        </div>
    </Layout>
</template>
