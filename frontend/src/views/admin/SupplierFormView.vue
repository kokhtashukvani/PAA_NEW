<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{ isEditMode ? 'ویرایش تامین کننده' : 'افزودن تامین کننده' }}
          </h2>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="card">
        <div class="card-body">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label">نام تامین کننده</label>
              <input type="text" class="form-control" v-model="form.name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">فرد مسئول</label>
              <input type="text" class="form-control" v-model="form.contact_person">
            </div>
            <div class="mb-3">
              <label class="form-label">ایمیل</label>
              <input type="email" class="form-control" v-model="form.email">
            </div>
            <div class="mb-3">
              <label class="form-label">تلفن</label>
              <input type="tel" class="form-control" v-model="form.phone">
            </div>
            <div class="mb-3">
              <label class="form-label">آدرس</label>
              <textarea class="form-control" rows="3" v-model="form.address"></textarea>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                ذخیره
              </button>
              <RouterLink :to="{ name: 'admin-suppliers' }" class="btn btn-link">لغو</RouterLink>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import apiClient from '@/services/api';

const props = defineProps({
  id: {
    type: String,
    default: null,
  },
});

const router = useRouter();
const loading = ref(false);
const form = ref({
  name: '',
  contact_person: '',
  email: '',
  phone: '',
  address: '',
});

const isEditMode = computed(() => !!props.id);

const fetchSupplier = async () => {
  if (!isEditMode.value) return;
  loading.value = true;
  try {
    const response = await apiClient.get(`/suppliers.php?id=${props.id}`);
    form.value = response.data;
  } catch (err) {
    console.error('Failed to fetch supplier', err);
    alert('Failed to load supplier data.');
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEditMode.value) {
      await apiClient.put(`/suppliers.php?id=${props.id}`, form.value);
    } else {
      await apiClient.post('/suppliers.php', form.value);
    }
    router.push({ name: 'admin-suppliers' });
  } catch (err) {
    console.error('Failed to save supplier', err);
    alert('Failed to save supplier.');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchSupplier);
</script>
