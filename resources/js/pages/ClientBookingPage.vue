<template>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Book an Appointment</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium mb-2">Service</label>
          <select v-model="selectedServiceId" class="w-full border rounded px-3 py-2">
            <option value="">Select a service</option>
            <option v-for="service in services" :key="service.id" :value="service.id">
              {{ service.name }} ({{ service.duration_minutes }} min)
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Date</label>
          <input v-model="selectedDate" type="date" class="w-full border rounded px-3 py-2">
        </div>
      </div>

      <button
        @click="loadSlots"
        :disabled="!selectedServiceId || !selectedDate || loading"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300"
      >
        {{ loading ? 'Loading...' : 'Load Slots' }}
      </button>
    </div>

    <div v-if="slots.length > 0" class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Available Slots</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <button
          v-for="slot in slots"
          :key="slot.start"
          @click="selectedSlot = slot"
          :class="['px-3 py-2 rounded text-sm border',
                   selectedSlot?.start === slot.start
                   ? 'bg-blue-500 text-white border-blue-500'
                   : 'bg-white hover:bg-gray-50 border-gray-300']"
        >
          {{ formatTime(slot.start) }} - {{ formatTime(slot.end) }}
        </button>
      </div>
    </div>

    <div v-if="selectedSlot" class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-4">Complete Booking</h3>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Email</label>
        <input
          v-model="email"
          type="email"
          placeholder="your@email.com"
          class="w-full border rounded px-3 py-2"
        >
      </div>

      <button
        @click="bookAppointment"
        :disabled="!email || booking"
        class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 disabled:bg-gray-300"
      >
        {{ booking ? 'Booking...' : 'Book Appointment' }}
      </button>

      <div v-if="message" :class="['mt-4 p-3 rounded', messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const services = ref([])
const selectedServiceId = ref('')
const selectedDate = ref('')
const slots = ref([])
const selectedSlot = ref(null)
const email = ref('')
const loading = ref(false)
const booking = ref(false)
const message = ref('')
const messageType = ref('')

onMounted(() => {
  loadServices()
})

async function loadServices() {
  try {
    const response = await fetch('/api/services')
    services.value = await response.json()
  } catch (error) {
    console.error('Error loading services:', error)
  }
}

async function loadSlots() {
  if (!selectedServiceId.value || !selectedDate.value) return

  loading.value = true
  try {
    const response = await fetch(`/api/availability?date=${selectedDate.value}&service_id=${selectedServiceId.value}`)
    slots.value = await response.json()
    selectedSlot.value = null
    message.value = ''
  } catch (error) {
    console.error('Error loading slots:', error)
  } finally {
    loading.value = false
  }
}

async function bookAppointment() {
  if (!selectedSlot.value || !email.value) return

  booking.value = true
  message.value = ''

  try {
    const response = await fetch('/api/bookings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        service_id: selectedServiceId.value,
        start_at: selectedSlot.value.start,
        client_email: email.value
      })
    })

    if (response.ok) {
      message.value = 'Appointment booked successfully!'
      messageType.value = 'success'
      selectedSlot.value = null
      email.value = ''
      await loadSlots()
    } else if (response.status === 409) {
      const error = await response.json()
      message.value = error.message || 'This slot is no longer available'
      messageType.value = 'error'
    }
  } catch (error) {
    message.value = 'An error occurred while booking'
    messageType.value = 'error'
  } finally {
    booking.value = false
  }
}

function formatTime(dateTime) {
  return new Date(dateTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>