<script setup>
import Layout from './Shared/Layout.vue'
import axios from 'axios'
import { ref, reactive } from 'vue'

const jsonFile = ref(null)
const excelFile = ref(null)

const meta = ref(null)
const excelColumns = ref([])

const uploadingJson = ref(false)
const uploadingExcel = ref(false)

// Drag and drop state
const dragState = reactive({
  isDraggingJson: false,
  isDraggingExcel: false
})

function onJsonFileChange(e) {
  jsonFile.value = e.target?.files?.[0] || null
}

function onExcelFileChange(e) {
  excelFile.value = e.target?.files?.[0] || null
}

// Drag and drop handlers for JSON
function onJsonDragOver(e) {
  e.preventDefault()
  dragState.isDraggingJson = true
}

function onJsonDragLeave(e) {
  e.preventDefault()
  dragState.isDraggingJson = false
}

function onJsonDrop(e) {
  e.preventDefault()
  dragState.isDraggingJson = false
  
  const files = e.dataTransfer.files
  if (files.length > 0 && files[0].type === 'application/json') {
    jsonFile.value = files[0]
  }
}

// Drag and drop handlers for Excel
function onExcelDragOver(e) {
  e.preventDefault()
  dragState.isDraggingExcel = true
}

function onExcelDragLeave(e) {
  e.preventDefault()
  dragState.isDraggingExcel = false
}

function onExcelDrop(e) {
  e.preventDefault()
  dragState.isDraggingExcel = false
  
  const files = e.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    const validTypes = [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel',
      'text/csv'
    ]
    if (validTypes.includes(file.type) || file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv')) {
      excelFile.value = file
    }
  }
}

async function onJsonUpload() {
  if (!jsonFile.value) return
  uploadingJson.value = true
  meta.value = null
  
  try {
    const form = new FormData()
    form.append('file', jsonFile.value)
    const { data } = await axios.post('/api/upload/json', form)
    meta.value = data
  } catch (error) {
    console.error('Upload failed:', error)
    meta.value = {
      status: 'error',
      message: error.response?.data?.message || 'Upload failed'
    }
  } finally {
    uploadingJson.value = false
  }
}

async function onExcelUpload() {
  if (!excelFile.value) return
  uploadingExcel.value = true
  excelColumns.value = []
  
  try {
    const form = new FormData()
    form.append('file', excelFile.value)
    const { data } = await axios.post('/api/upload/excel', form)
    excelColumns.value = data.columns || []
  } catch (error) {
    console.error('Upload failed:', error)
    excelColumns.value = []
  } finally {
    uploadingExcel.value = false
  }
}

function resetJsonUpload() {
  jsonFile.value = null
  meta.value = null
}

function resetExcelUpload() {
  excelFile.value = null
  excelColumns.value = []
}
</script>

<template>
  <Layout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">ECRF File Upload</h1>
        <p class="text-lg text-gray-600">Upload your project JSON files and Excel data files to get started</p>
      </div>

      <div class="grid gap-8 lg:grid-cols-2">
        <!-- JSON Upload -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
            <h2 class="text-xl font-semibold text-blue-900">Project JSON Upload</h2>
            <p class="text-sm text-blue-700 mt-1">Upload your eCRF project definition file</p>
          </div>
          
          <div class="p-6">
            <!-- Drag and Drop Zone -->
            <div 
              class="border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200"
              :class="{
                'border-blue-400 bg-blue-50': dragState.isDraggingJson,
                'border-gray-300 hover:border-gray-400': !dragState.isDraggingJson
              }"
              @dragover="onJsonDragOver"
              @dragleave="onJsonDragLeave"
              @drop="onJsonDrop"
            >
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                
                <div v-if="!jsonFile">
                  <p class="text-lg font-medium text-gray-700 mb-2">Drop your JSON file here</p>
                  <p class="text-sm text-gray-500 mb-4">or click to browse</p>
                  <input 
                    type="file" 
                    accept="application/json,.json" 
                    @change="onJsonFileChange" 
                    class="hidden"
                    id="jsonFileInput"
                  />
                  <label 
                    for="jsonFileInput"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 cursor-pointer"
                  >
                    Choose File
                  </label>
                </div>
                
                <div v-else class="w-full">
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <span class="text-sm text-gray-700">{{ jsonFile.name }}</span>
                    </div>
                    <button 
                      @click="resetJsonUpload"
                      class="text-red-500 hover:text-red-700"
                    >
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <button 
              class="mt-4 w-full py-3 px-4 rounded-md text-white font-medium"
              :class="{
                'bg-blue-600 hover:bg-blue-700': !uploadingJson && jsonFile,
                'bg-gray-400 cursor-not-allowed': uploadingJson || !jsonFile
              }"
              :disabled="uploadingJson || !jsonFile"
              @click="onJsonUpload"
            >
              {{ uploadingJson ? 'Uploading...' : 'Upload & Parse JSON' }}
            </button>

            <!-- JSON Upload Results -->
            <div v-if="meta" class="mt-6 p-4 rounded-md" :class="{
              'bg-green-50 border border-green-200': meta.status === 'ok',
              'bg-yellow-50 border border-yellow-200': meta.status === 'exists',
              'bg-red-50 border border-red-200': meta.status === 'error'
            }">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg v-if="meta.status === 'ok'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <svg v-else-if="meta.status === 'exists'" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium" :class="{
                    'text-green-800': meta.status === 'ok',
                    'text-yellow-800': meta.status === 'exists',
                    'text-red-800': meta.status === 'error'
                  }">
                    {{ meta.status === 'ok' ? 'Success!' : meta.status === 'exists' ? 'Already Exists' : 'Error' }}
                  </h3>
                  <div class="mt-2 text-sm space-y-1" :class="{
                    'text-green-700': meta.status === 'ok',
                    'text-yellow-700': meta.status === 'exists',
                    'text-red-700': meta.status === 'error'
                  }">
                    <p v-if="meta.project_name"><strong>Project:</strong> {{ meta.project_name }}</p>
                    <p v-if="meta.forms"><strong>Forms:</strong> {{ meta.forms }}</p>
                    <p v-if="meta.version"><strong>Version:</strong> {{ meta.version }}</p>
                    <p v-if="meta.project_id"><strong>Project ID:</strong> {{ meta.project_id }}</p>
                    <p v-if="meta.message">{{ meta.message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Excel Upload -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="bg-green-50 px-6 py-4 border-b border-green-100">
            <h2 class="text-xl font-semibold text-green-900">Excel Data Upload</h2>
            <p class="text-sm text-green-700 mt-1">Upload Excel/CSV files for data import</p>
          </div>
          
          <div class="p-6">
            <!-- Drag and Drop Zone -->
            <div 
              class="border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200"
              :class="{
                'border-green-400 bg-green-50': dragState.isDraggingExcel,
                'border-gray-300 hover:border-gray-400': !dragState.isDraggingExcel
              }"
              @dragover="onExcelDragOver"
              @dragleave="onExcelDragLeave"
              @drop="onExcelDrop"
            >
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v14"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 5v14"></path>
                </svg>
                
                <div v-if="!excelFile">
                  <p class="text-lg font-medium text-gray-700 mb-2">Drop your Excel/CSV file here</p>
                  <p class="text-sm text-gray-500 mb-4">or click to browse</p>
                  <input 
                    type="file" 
                    accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv" 
                    @change="onExcelFileChange" 
                    class="hidden"
                    id="excelFileInput"
                  />
                  <label 
                    for="excelFileInput"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 cursor-pointer"
                  >
                    Choose File
                  </label>
                </div>
                
                <div v-else class="w-full">
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <span class="text-sm text-gray-700">{{ excelFile.name }}</span>
                    </div>
                    <button 
                      @click="resetExcelUpload"
                      class="text-red-500 hover:text-red-700"
                    >
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <button 
              class="mt-4 w-full py-3 px-4 rounded-md text-white font-medium"
              :class="{
                'bg-green-600 hover:bg-green-700': !uploadingExcel && excelFile,
                'bg-gray-400 cursor-not-allowed': uploadingExcel || !excelFile
              }"
              :disabled="uploadingExcel || !excelFile"
              @click="onExcelUpload"
            >
              {{ uploadingExcel ? 'Reading...' : 'Read Columns' }}
            </button>

            <!-- Excel Upload Results -->
            <div v-if="excelColumns.length" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
              <h3 class="text-sm font-medium text-green-800 mb-2">Detected Columns ({{ excelColumns.length }})</h3>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="col in excelColumns" 
                  :key="col"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  {{ col }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div v-if="meta && meta.status === 'ok'" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">What's Next?</h3>
            <div class="mt-2 text-sm text-blue-700">
              <p>Your project has been successfully uploaded! You can now:</p>
              <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Go to the <a href="/wizard" class="font-medium text-blue-600 hover:text-blue-500">Import Wizard</a> to map and import data</li>
                <li>Visit the <a href="/output" class="font-medium text-blue-600 hover:text-blue-500">Export Generator</a> to create data exports</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
