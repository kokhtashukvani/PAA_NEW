<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            مدیریت فرم‌ها
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <button class="btn btn-primary" @click="handleCreateForm">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg>
              ایجاد فرم جدید
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="page-body">
       <div class="card">
        <div v-if="loading" class="progress progress-sm card-progress">
          <div class="progress-bar-indeterminate"></div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>نام فرم</th>
                <th>توضیحات</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="error">
                <td colspan="3" class="text-center text-danger">{{ error }}</td>
              </tr>
              <tr v-if="!loading && forms.length === 0 && !error">
                <td colspan="3" class="text-center">هیچ فرمی یافت نشد.</td>
              </tr>
              <tr v-for="form in forms" :key="form.id">
                <td>{{ form.name }}</td>
                <td class="text-muted">{{ form.description }}</td>
                <td>
                  <RouterLink :to="{ name: 'admin-forms-edit', params: { id: form.id } }" class="btn">
                    ویرایش
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import apiClient from '@/services/api';

const forms = ref([]);
const loading = ref(true);
const error = ref(null);
const router = useRouter();

const fetchForms = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await apiClient.get('/forms.php');
    forms.value = response.data;
  } catch (err) {
    error.value = 'Failed to load forms.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const handleCreateForm = async () => {
    const formName = prompt("لطفا نام فرم جدید را وارد کنید:", "فرم درخواست خرید");
    if (!formName) return;

    try {
        const response = await apiClient.post('/forms.php', { name: formName, description: 'یک فرم جدید' });
        const newForm = response.data;
        router.push({ name: 'admin-forms-edit', params: { id: newForm.id } });
    } catch (err) {
        alert('Failed to create form.');
        console.error(err);
    }
}

onMounted(fetchForms);
</script>
