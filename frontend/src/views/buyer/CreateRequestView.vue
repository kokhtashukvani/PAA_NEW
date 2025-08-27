<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{ form.name || 'ثبت درخواست خرید' }}
          </h2>
          <p class="text-muted">{{ form.description }}</p>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="card">
        <div v-if="loading" class="progress progress-sm card-progress">
          <div class="progress-bar-indeterminate"></div>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleSubmit">
            <div v-if="error" class="alert alert-danger">{{ error }}</div>

            <template v-for="field in form.fields" :key="field.id">
              <div class="mb-3">
                <label :for="field.name" class="form-label">
                  {{ field.label }}
                  <span v-if="field.is_required" class="text-danger">*</span>
                </label>

                <!-- Text Input -->
                <input
                  v-if="field.type === 'text'"
                  :id="field.name"
                  type="text"
                  class="form-control"
                  :required="field.is_required"
                  v-model="formData[field.name]">

                <!-- Textarea -->
                <textarea
                  v-if="field.type === 'textarea'"
                  :id="field.name"
                  class="form-control"
                  :required="field.is_required"
                  rows="3"
                  v-model="formData[field.name]"></textarea>

                <!-- Number Input -->
                <input
                  v-if="field.type === 'number'"
                  :id="field.name"
                  type="number"
                  class="form-control"
                  :required="field.is_required"
                  v-model="formData[field.name]">

                <!-- Date Input -->
                <input
                  v-if="field.type === 'date'"
                  :id="field.name"
                  type="date"
                  class="form-control"
                  :required="field.is_required"
                  v-model="formData[field.name]">
              </div>
            </template>

            <div class="form-footer">
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                ارسال درخواست
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/services/api';

const router = useRouter();
const form = ref({});
const formData = ref({});
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);

// In a real app, this ID might come from a route param or a config file
const PURCHASE_REQUEST_FORM_ID = 1;

const fetchFormStructure = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await apiClient.get(`/forms.php?id=${PURCHASE_REQUEST_FORM_ID}`);
    form.value = response.data;
    // Initialize formData with null values for each field
    if (form.value.fields) {
      form.value.fields.forEach(field => {
        formData.value[field.name] = null;
      });
    }
  } catch (err) {
    console.error('Failed to fetch form structure', err);
    error.value = 'خطا در بارگذاری ساختار فرم. لطفا مطمئن شوید فرم "درخواست خرید" با شناسه 1 در سیستم وجود دارد.';
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  submitting.value = true;
  error.value = null;
  try {
    await apiClient.post('/requests.php', {
      form_id: PURCHASE_REQUEST_FORM_ID,
      data: formData.value,
    });
    alert('درخواست شما با موفقیت ثبت شد.');
    router.push({ name: 'home' });
  } catch (err) {
    console.error('Failed to submit request', err);
    error.value = err.response?.data?.error || 'خطا در ثبت درخواست.';
  } finally {
    submitting.value = false;
  }
};

onMounted(fetchFormStructure);
</script>
