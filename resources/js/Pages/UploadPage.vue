<script setup>
import Layout from './Shared/Layout.vue'
import axios from 'axios'
import { ref } from 'vue'

const jsonFile = ref(null)
const excelFile = ref(null)

const meta = ref(null)
const excelColumns = ref([])

const uploadingJson = ref(false)
const uploadingExcel = ref(false)

async function onJsonUpload() {
    if (!jsonFile.value) return
    uploadingJson.value = true
    try {
        const form = new FormData()
        form.append('file', jsonFile.value)
        const { data } = await axios.post('/api/upload/json', form)
        meta.value = data
    } finally { uploadingJson.value = false }
}

async function onExcelUpload() {
    if (!excelFile.value) return
    uploadingExcel.value = true
    try {
        const form = new FormData()
        form.append('file', excelFile.value)
        const { data } = await axios.post('/api/upload/excel', form)
        excelColumns.value = data.columns || []
    } finally { uploadingExcel.value = false }
}
</script>

<template>
    <Layout>
        <div class="grid gap-6 md:grid-cols-2">
            <!-- JSON Upload -->
            <div class="bg-white p-4 rounded-2xl shadow">
                <h2 class="font-semibold mb-3">Upload Project JSON</h2>
                <input type="file" accept="application/json" @change="e => jsonFile.value = e.target.files[0]" />
                <button class="mt-3 px-4 py-2 rounded-xl bg-blue-600 text-white" :disabled="uploadingJson || !jsonFile"
                    @click="onJsonUpload">
                    {{ uploadingJson ? 'Uploading…' : 'Upload & Parse' }}
                </button>

                <div v-if="meta" class="mt-4 text-sm">
                    <div class="font-medium">Project Metadata</div>
                    <div>Name: {{ meta.project_name }}</div>
                    <div>Forms: {{ meta.forms }}</div>
                    <div>Version: {{ meta.version }}</div>
                    <div>Project ID: {{ meta.project_id }}</div>
                </div>
            </div>

            <!-- Excel Upload -->
            <div class="bg-white p-4 rounded-2xl shadow">
                <h2 class="font-semibold mb-3">Upload Excel (for import mapping)</h2>
                <input type="file" accept=".xlsx,.xls,.csv" @change="e => excelFile.value = e.target.files[0]" />
                <button class="mt-3 px-4 py-2 rounded-xl bg-emerald-600 text-white"
                    :disabled="uploadingExcel || !excelFile" @click="onExcelUpload">
                    {{ uploadingExcel ? 'Reading…' : 'Read Columns' }}
                </button>

                <div v-if="excelColumns.length" class="mt-4">
                    <div class="font-medium text-sm mb-1">Detected Columns</div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="c in excelColumns" :key="c" class="px-2 py-1 bg-gray-100 rounded">
                            {{ c }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
