<script setup>
import { computed } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const logoutForm = useForm({})

function logout() {
  logoutForm.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h1 class="text-xl font-bold text-gray-900">ECRF System</h1>
            </div>
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <a href="/" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Upload
              </a>
              <a href="/wizard" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Import Wizard
              </a>
              <a href="/output" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Export
              </a>
            </div>
          </div>

          <div class="flex items-center">
            <div v-if="user" class="flex items-center space-x-4">
              <span class="text-sm text-gray-700">{{ user.name }}</span>
              <button
                @click="logout"
                :disabled="logoutForm.processing"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                Logout
              </button>
            </div>
            <div v-else class="flex items-center space-x-4">
              <a href="/login" class="text-gray-500 hover:text-gray-700">
                Login
              </a>
              <a href="/register" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Register
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
          <p>&copy; 2024 ECRF System. Electronic Case Report Form Management Platform.</p>
          <p class="mt-1">Secure, efficient, and user-friendly clinical data management.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
