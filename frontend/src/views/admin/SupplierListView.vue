<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            مدیریت تامین کنندگان
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <RouterLink :to="{ name: 'admin-suppliers-create' }" class="btn btn-primary d-none d-sm-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 5l0 14"></path><path d="M5 12l14 0"></path></svg>
              افزودن تامین کننده
            </RouterLink>
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
                <th>نام</th>
                <th>مسئول</th>
                <th>ایمیل</th>
                <th>تلفن</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="error">
                <td colspan="5" class="text-center text-danger">{{ error }}</td>
              </tr>
              <tr v-if="!loading && suppliers.length === 0 && !error">
                <td colspan="5" class="text-center">هیچ تامین کننده‌ای یافت نشد.</td>
              </tr>
              <tr v-for="supplier in suppliers" :key="supplier.id">
                <td>{{ supplier.name }}</td>
                <td class="text-muted">{{ supplier.contact_person }}</td>
                <td class="text-muted">{{ supplier.email }}</td>
                <td class="text-muted">{{ supplier.phone }}</td>
                <td>
                  <div class="btn-list flex-nowrap">
                    <RouterLink :to="{ name: 'admin-suppliers-edit', params: { id: supplier.id } }" class="btn">
                      ویرایش
                    </RouterLink>
                    <button class="btn btn-danger" @click="handleDelete(supplier.id)">
                      حذف
                    </button>
                  </div>
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
import { RouterLink } from 'vue-router';
import apiClient from '@/services/api';

const suppliers = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchSuppliers = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await apiClient.get('/suppliers.php');
    suppliers.value = response.data;
  } catch (err) {
    error.value = 'Failed to load suppliers.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm('آیا از حذف این تامین کننده اطمینان دارید؟')) {
    return;
  }
  try {
    await apiClient.delete(`/suppliers.php?id=${id}`); // Note: Passing id in URL for DELETE
    suppliers.value = suppliers.value.filter(s => s.id !== id);
  } catch (err) {
    alert('Failed to delete supplier.');
    console.error(err);
  }
};

onMounted(fetchSuppliers);
</script>
