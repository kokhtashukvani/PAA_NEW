<template>
  <div class="page page-center">
    <div class="container container-tight py-4">
      <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark">
          <img src="@/assets/logo.svg" height="36" alt="Tabler" />
        </a>
      </div>
      <div class="card card-md">
        <div class="card-body">
          <h2 class="h2 text-center mb-4">ایجاد حساب کاربری جدید</h2>
          <form @submit.prevent="handleRegister" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">نام کاربری</label>
              <input type="text" class="form-control" v-model="username" placeholder="نام کاربری خود را وارد کنید" required>
            </div>
            <div class="mb-2">
              <label class="form-label">رمز عبور</label>
              <input type="password" class="form-control" v-model="password" placeholder="حداقل ۸ کاراکتر" required>
            </div>
             <div v-if="errorMessage" class="alert alert-danger" role="alert">
              {{ errorMessage }}
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                ثبت نام
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center text-muted mt-3">
        قبلا ثبت نام کرده‌اید؟ <router-link to="/login">وارد شوید</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const username = ref('');
const password = ref('');
const errorMessage = ref(null);
const loading = ref(false);

const authStore = useAuthStore();

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = null;
  try {
    await authStore.register({
      username: username.value,
      password: password.value,
    });
    // The store action handles the redirect to login on success
  } catch (error) {
    errorMessage.value = error.response?.data?.error || 'An unexpected error occurred during registration.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.page-center {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f5f7fb;
}
</style>
