<script setup>
import Layout from './Shared/Layout.vue'
import axios from 'axios'
import { ref, onMounted } from 'vue'

const projects = ref([])
const selectedProjectId = ref(null)
const exportType = ref('scheduler')
const exporting = ref(false)
const statusMsg = ref(null)

const exportTypes = [
  { value: 'scheduler', label: 'Scheduler Structure', description: 'Export project structure for scheduling systems' },
  { value: 'form', label: 'Form Structure', description: 'Export detailed form definitions and fields' },
  { value: 'conditional', label: 'Conditional Fields', description: 'Export fields with conditional logic only' }
]

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/forms')
    // Group forms by project
    const projectMap = new Map()
    
    data.forEach(form => {
      const projectId = form.project_id
      if (!projectMap.has(projectId)) {
        projectMap.set(projectId, {
          id: projectId,
          name: form.project?.name || `Project ${projectId}`,
          version: form.project?.version || 'v1.0',
          forms: []
        })
      }
      projectMap.get(projectId).forms.push(form)
    })
    
    projects.value = Array.from(projectMap.values())
  } catch (error) {
    statusMsg.value = 'Failed to load projects'
    console.error('Failed to load projects:', error)
  }
})

async function generateExport() {
  if (!selectedProjectId.value || !exportType.value) {
    statusMsg.value = 'Please select a project and export type'
    return
  }
  
  exporting.value = true
  statusMsg.value = 'Generating export...'
  
  try {
    const response = await axios.get(`/api/generate/${exportType.value}`, {
      params: { project_id: selectedProjectId.value },
      responseType: 'blob'
    })
    
    // Create download link
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    // Generate filename based on selected project and type
    const project = projects.value.find(p => p.id == selectedProjectId.value)
    const projectName = project?.name?.replace(/[^a-zA-Z0-9]/g, '_') || 'project'
    const version = project?.version || 'v1.0'
    const date = new Date().toISOString().split('T')[0]
    
    const filename = `${projectName}_${exportType.value}_${version}_${date}.xlsx`
    link.download = filename
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    statusMsg.value = `Export completed successfully: ${filename}`
  } catch (error) {
    statusMsg.value = `Export failed: ${error.response?.data?.message || error.message}`
    console.error('Export failed:', error)
  } finally {
    exporting.value = false
  }
}

function getSelectedProject() {
  return projects.value.find(p => p.id == selectedProjectId.value)
}

function getSelectedExportType() {
  return exportTypes.find(t => t.value === exportType.value)
}
</script>

<template>
  <Layout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Export Generator</h1>
        <p class="text-lg text-gray-600">Generate structured exports from your ECRF projects</p>
      </div>

      <!-- Export Configuration -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
          <h2 class="text-xl font-semibold text-indigo-900">Export Configuration</h2>
          <p class="text-sm text-indigo-700 mt-1">Configure your export settings</p>
        </div>
        
        <div class="p-6 space-y-6">
          <!-- Project Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Project
            </label>
            <select 
              v-model="selectedProjectId"
              class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option :value="null">-- Choose a project --</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.name }} ({{ project.version }}) - {{ project.forms.length }} forms
              </option>
            </select>
            
            <!-- Project Preview -->
            <div v-if="getSelectedProject()" class="mt-3 p-3 bg-gray-50 rounded-md">
              <h4 class="text-sm font-medium text-gray-800 mb-2">Project Details</h4>
              <div class="text-sm text-gray-600 space-y-1">
                <p><strong>Name:</strong> {{ getSelectedProject().name }}</p>
                <p><strong>Version:</strong> {{ getSelectedProject().version }}</p>
                <p><strong>Forms:</strong> {{ getSelectedProject().forms.length }}</p>
                <div v-if="getSelectedProject().forms.length > 0">
                  <p><strong>Form List:</strong></p>
                  <ul class="list-disc list-inside ml-4 mt-1">
                    <li v-for="form in getSelectedProject().forms" :key="form.id">
                      {{ form.title }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Export Type Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Export Type
            </label>
            <div class="space-y-3">
              <div 
                v-for="type in exportTypes" 
                :key="type.value"
                class="flex items-start space-x-3"
              >
                <input
                  :id="type.value"
                  v-model="exportType"
                  :value="type.value"
                  type="radio"
                  class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                />
                <div class="flex-1">
                  <label :for="type.value" class="block text-sm font-medium text-gray-700 cursor-pointer">
                    {{ type.label }}
                  </label>
                  <p class="text-sm text-gray-500 mt-1">{{ type.description }}</p>
                </div>
              </div>
            </div>
            
            <!-- Export Type Details -->
            <div v-if="getSelectedExportType()" class="mt-3 p-3 bg-indigo-50 rounded-md">
              <h4 class="text-sm font-medium text-indigo-800 mb-2">Export Details</h4>
              <div class="text-sm text-indigo-700">
                <div v-if="exportType === 'scheduler'">
                  <p><strong>Includes:</strong> Project structure, scheduling metadata, form hierarchy</p>
                  <p><strong>Format:</strong> Excel with structured columns for scheduling systems</p>
                  <p><strong>Use Case:</strong> Integration with clinical trial scheduling platforms</p>
                </div>
                <div v-else-if="exportType === 'form'">
                  <p><strong>Includes:</strong> Complete form definitions, field specifications, validation rules</p>
                  <p><strong>Format:</strong> Excel with detailed field metadata and options</p>
                  <p><strong>Use Case:</strong> Form documentation and system integration</p>
                </div>
                <div v-else-if="exportType === 'conditional'">
                  <p><strong>Includes:</strong> Fields with conditional logic, visibility rules, edit checks</p>
                  <p><strong>Format:</strong> Excel with logic definitions and dependencies</p>
                  <p><strong>Use Case:</strong> Complex form logic documentation and validation</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Export Button -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
              <span v-if="!selectedProjectId || !exportType">
                Please select a project and export type to continue
              </span>
              <span v-else>
                Ready to export {{ getSelectedExportType()?.label.toLowerCase() }} for {{ getSelectedProject()?.name }}
              </span>
            </div>
            
            <button
              @click="generateExport"
              :disabled="exporting || !selectedProjectId || !exportType"
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white"
              :class="{
                'bg-indigo-600 hover:bg-indigo-700': !exporting && selectedProjectId && exportType,
                'bg-gray-400 cursor-not-allowed': exporting || !selectedProjectId || !exportType
              }"
            >
              <svg v-if="exporting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ exporting ? 'Generating...' : 'Generate Export' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Export History/Information -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">Export Information</h2>
        </div>
        
        <div class="p-6">
          <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
              <div class="bg-blue-100 rounded-full p-3 w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Structured Data</h3>
              <p class="text-sm text-gray-600">Exports are formatted for easy integration with external systems and tools.</p>
            </div>
            
            <div class="text-center">
              <div class="bg-green-100 rounded-full p-3 w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Validated Output</h3>
              <p class="text-sm text-gray-600">All exports include validation and consistency checks for data integrity.</p>
            </div>
            
            <div class="text-center">
              <div class="bg-purple-100 rounded-full p-3 w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Multiple Formats</h3>
              <p class="text-sm text-gray-600">Choose from different export types optimized for specific use cases.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Messages -->
      <div v-if="statusMsg" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-700">{{ statusMsg }}</p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>