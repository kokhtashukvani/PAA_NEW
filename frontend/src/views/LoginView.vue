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
          <h2 class="h2 text-center mb-4">ورود به پنل کاربری</h2>
          <form @submit.prevent="handleLogin" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">نام کاربری</label>
              <input type="text" class="form-control" v-model="username" placeholder="نام کاربری خود را وارد کنید" required>
            </div>
            <div class="mb-2">
              <label class="form-label">
                رمز عبور
              </label>
              <input type="password" class="form-control" v-model="password" placeholder="رمز عبور خود را وارد کنید" required>
            </div>
            <div v-if="errorMessage" class="alert alert-danger" role="alert">
              {{ errorMessage }}
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                ورود
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center text-muted mt-3">
        حساب کاربری ندارید؟ <router-link to="/register">ثبت نام کنید</router-link>
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

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = null;
  try {
    await authStore.login({
      username: username.value,
      password: password.value,
    });
    // The store action handles the redirect on success
  } catch (error) {
    errorMessage.value = error.response?.data?.error || 'An unexpected error occurred.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Scoped styles for the login page */
.page-center {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f5f7fb;
}
</style>
