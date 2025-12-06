<template>
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Manage Working Rules</h1>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Add Working Rule</h2>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Type</label>
          <select v-model="form.type" @change="form.weekday = null; form.date = ''" class="w-full border rounded px-3 py-2">
            <option value="">Select type</option>
            <option value="weekly">Weekly</option>
            <option value="date">Date</option>
          </select>
        </div>

        <div v-if="form.type === 'weekly'">
          <label class="block text-sm font-medium mb-2">Weekday</label>
          <select v-model="form.weekday" class="w-full border rounded px-3 py-2">
            <option value="">Select weekday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
            <option value="7">Sunday</option>
          </select>
        </div>

        <div v-if="form.type === 'date'">
          <label class="block text-sm font-medium mb-2">Date</label>
          <input v-model="form.date" type="date" class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Start Time</label>
            <input v-model="form.start_time" type="time" class="w-full border rounded px-3 py-2">
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">End Time</label>
            <input v-model="form.end_time" type="time" class="w-full border rounded px-3 py-2">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Slot Interval (minutes)</label>
          <input v-model.number="form.slot_interval" type="number" min="1" class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="flex items-center">
            <input v-model="form.active" type="checkbox" class="mr-2">
            <span class="text-sm font-medium">Active</span>
          </label>
        </div>

        <button
          @click="submitRule"
          :disabled="!canSubmit || submitting"
          class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300"
        >
          {{ submitting ? 'Creating...' : 'Create Rule' }}
        </button>

        <div v-if="message" :class="['p-3 rounded', message.includes('Error') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800']">
          {{ message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const form = ref({
  type: '',
  weekday: '',
  date: '',
  start_time: '',
  end_time: '',
  slot_interval: 30,
  active: true
})

const submitting = ref(false)
const message = ref('')

const canSubmit = computed(() => {
  return form.value.type &&
         (form.value.type === 'weekly' ? form.value.weekday : form.value.date) &&
         form.value.start_time &&
         form.value.end_time &&
         form.value.slot_interval > 0
})

async function submitRule() {
  if (!canSubmit.value) return

  submitting.value = true
  message.value = ''

  const payload = {
    type: form.value.type,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    slot_interval: form.value.slot_interval,
    active: form.value.active
  }

  if (form.value.type === 'weekly') {
    payload.weekday = form.value.weekday
  } else {
    payload.date = form.value.date
  }

  try {
    const response = await fetch('/api/working-rules', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    })

    if (response.ok) {
      message.value = 'Working rule created successfully!'
      resetForm()
    } else {
      const error = await response.json()
      message.value = `Error: ${error.message || 'Failed to create rule'}`
    }
  } catch (error) {
    message.value = `Error: ${error.message}`
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  form.value = {
    type: '',
    weekday: '',
    date: '',
    start_time: '',
    end_time: '',
    slot_interval: 30,
    active: true
  }
}
</script>