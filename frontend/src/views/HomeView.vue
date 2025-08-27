<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            داشبورد
          </h2>
          <div class="text-muted mt-1">لیست درخواست‌های خرید شما.</div>
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
                <th>شناسه درخواست</th>
                <th>نوع فرم</th>
                <th>تاریخ ثبت</th>
                <th>وضعیت</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="error">
                <td colspan="4" class="text-center text-danger">{{ error }}</td>
              </tr>
              <tr v-if="!loading && requests.length === 0 && !error">
                <td colspan="4" class="text-center">هیچ درخواستی ثبت نشده است.</td>
              </tr>
              <tr v-for="request in requests" :key="request.id">
                <td>#{{ request.id }}</td>
                <td>{{ request.form_name }}</td>
                <td class="text-muted">{{ new Date(request.created_at).toLocaleDateString('fa-IR') }}</td>
                <td>
                  <span class="badge" :class="statusBadgeClass(request.status)">
                    {{ translateStatus(request.status) }}
                  </span>
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
import apiClient from '@/services/api';

const requests = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchRequests = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await apiClient.get('/requests.php');
    requests.value = response.data;
  } catch (err) {
    error.value = 'Failed to load purchase requests.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const statusTranslations = {
  pending: 'در انتظار بررسی',
  approved: 'تایید شده',
  rejected: 'رد شده',
};

const statusBadgeClasses = {
  pending: 'bg-yellow-lt',
  approved: 'bg-green-lt',
  rejected: 'bg-red-lt',
};

const translateStatus = (status) => statusTranslations[status] || status;
const statusBadgeClass = (status) => statusBadgeClasses[status] || 'bg-secondary-lt';


onMounted(fetchRequests);
</script>
