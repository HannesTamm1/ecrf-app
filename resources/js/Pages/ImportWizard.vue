<script setup>
import Layout from './Shared/Layout.vue'
import axios from 'axios'
import { ref, onMounted, computed } from 'vue'

const forms = ref([])
const selectedFormId = ref(null)
const excelColumns = ref([])
const mapping = ref({}) // ExcelCol -> field.name

const fields = ref([])
const statusMsg = ref(null)
const projectId = ref(null)

const excelFile = ref(null)
const validating = ref(false)
const importing = ref(false)

onMounted(async () => {
    // try to fetch forms (project optional; let user filter later)
    const { data } = await axios.get('/api/forms')
    forms.value = data
})

async function fetchFields() {
    fields.value = []
    if (!selectedFormId.value) return
    // simple fields list (we’ll fetch from /api/forms? and then per-form fields via a tiny endpoint)
    const { data } = await axios.get('/api/forms', { params: {} })
    const f = data.find(d => d.id === selectedFormId.value)
    if (!f) return
    // ask backend for fields: quick inline endpoint via project JSON not included,
    // so we’ll load from a small custom endpoint in this page:
    const res = await axios.get(`/api/form-fields/${selectedFormId.value}`).catch(() => null)
    fields.value = res?.data || []
}

// Create the custom endpoint on the fly in your routes/controller if you want; meanwhile we’ll fallback:
onMounted(async () => {
    // fallback fields endpoint (defined below in a comment for controller snippet)
    try {
        await fetchFields()
    } catch { }
})

async function readExcelColumns() {
    if (!excelFile.value) return
    const formData = new FormData()
    formData.append('file', excelFile.value)
    const { data } = await axios.post('/api/upload/excel', formData)
    excelColumns.value = data.columns || []
    // naive auto-match by exact label/name
    for (const col of excelColumns.value) {
        const hit = fields.value.find(f =>
            [f.name, f.label].filter(Boolean).some(v => v.toLowerCase() === String(col).toLowerCase())
        )
        if (hit) mapping.value[col] = hit.name
    }
}

async function validateMapping() {
    validating.value = true
    try {
        const { data } = await axios.post('/api/map-columns', {
            form_id: selectedFormId.value,
            mappings: mapping.value
        })
        statusMsg.value = `${data.status}: ${data.valid_rows} rows valid${data.warnings?.length ? ' (' + data.warnings.join('; ') + ')' : ''}`
    } finally { validating.value = false }
}

async function importData() {
    importing.value = true
    try {
        const form = new FormData()
        form.append('form_id', selectedFormId.value)
        form.append('file', excelFile.value)
        form.append('mappings', new Blob([JSON.stringify(mapping.value)], { type: 'application/json' }))
        // Laravel doesn't parse json blob in multipart by default, so we send plain field instead:
        // (Controller expects 'mappings' array; we’ll convert via request->validate)
    } finally { /* we will do a simpler post below */ }
    try {
        const formData = new FormData()
        formData.append('form_id', selectedFormId.value)
        formData.append('file', excelFile.value)
        // send mappings as a separate field and decode server-side
        formData.append('mappings', JSON.stringify(mapping.value))

        // Override axios transform to parse 'mappings' JSON on server:
        const { data } = await axios.post('/api/import', formData, {
            transformRequest: [(data, headers) => data],
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        statusMsg.value = `Imported ${data.imported}. ${data.warnings?.length ? data.warnings.length + ' warnings' : 'No warnings'}`
    } finally {
        importing.value = false
    }
}
</script>

<template>
    <Layout>
        <div class="bg-white p-4 rounded-2xl shadow">
            <h2 class="font-semibold mb-4">Excel Import Wizard</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Target Form</label>
                    <select v-model="selectedFormId" class="mt-1 border rounded px-2 py-1 w-full" @change="fetchFields">
                        <option :value="null">-- Select --</option>
                        <option v-for="f in forms" :key="f.id" :value="f.id">{{ f.title }}</option>
                    </select>

                    <div class="mt-4">
                        <label class="block text-sm font-medium">Excel File</label>
                        <input type="file" accept=".xlsx,.xls,.csv"
                            @change="e => excelFile.value = e.target.files[0]" />
                        <button class="mt-3 px-3 py-2 rounded bg-emerald-600 text-white" :disabled="!excelFile"
                            @click="readExcelColumns">
                            Read Columns
                        </button>
                    </div>

                    <div v-if="statusMsg" class="mt-4 text-sm text-gray-700">{{ statusMsg }}</div>
                </div>

                <div>
                    <div class="font-medium mb-2">Mapping</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-xs font-semibold">Excel Column</div>
                        <div class="text-xs font-semibold">Form Field</div>

                        <template v-for="col in excelColumns" :key="col">
                            <div class="text-sm py-1">{{ col }}</div>
                            <div>
                                <select class="border rounded px-2 py-1 text-sm w-full" v-model="mapping[col]">
                                    <option :value="null">-- Unmapped --</option>
                                    <option v-for="ff in fields" :key="ff.name" :value="ff.name">
                                        {{ ff.name }} ({{ ff.label || 'no label' }})
                                    </option>
                                </select>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button class="px-3 py-2 rounded bg-blue-600 text-white" :disabled="!selectedFormId"
                            @click="validateMapping">
                            Validate Mapping
                        </button>
                        <button class="px-3 py-2 rounded bg-indigo-600 text-white"
                            :disabled="!selectedFormId || !excelColumns.length" @click="importData">
                            Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
