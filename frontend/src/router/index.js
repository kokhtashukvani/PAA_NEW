import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardLayout from '../layouts/DashboardLayout.vue'

// Admin Views
const SupplierListView = () => import('../views/admin/SupplierListView.vue');
const SupplierFormView = () => import('../views/admin/SupplierFormView.vue');
const FormListView = () => import('../views/admin/FormListView.vue');
const FormBuilderView = () => import('../views/admin/FormBuilderView.vue');

// Buyer Views
const CreateRequestView = () => import('../views/buyer/CreateRequestView.vue');

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'home',
          component: HomeView,
        },
        {
          path: 'about',
          name: 'about',
          component: () => import('../views/AboutView.vue'),
        },
        // Admin Routes
        {
          path: 'admin/suppliers',
          name: 'admin-suppliers',
          component: SupplierListView,
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/suppliers/create',
          name: 'admin-suppliers-create',
          component: SupplierFormView,
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/suppliers/edit/:id',
          name: 'admin-suppliers-edit',
          component: SupplierFormView,
          meta: { requiresAdmin: true },
          props: true, // Pass route params as props to the component
        },
        {
          path: 'admin/forms',
          name: 'admin-forms',
          component: FormListView,
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/forms/edit/:id',
          name: 'admin-forms-edit',
          component: FormBuilderView,
          meta: { requiresAdmin: true },
          props: true,
        },
        // Buyer Routes
        {
          path: 'requests/create',
          name: 'create-request',
          component: CreateRequestView,
        },
      ],
    },
  ]
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated
  const isAdmin = authStore.userRole === 'admin'

  // Route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login' })
  }

  // Route requires admin privileges
  if (to.meta.requiresAdmin && !isAdmin) {
    // Redirect non-admins to the home page
    return next({ name: 'home' })
  }

  // Route is for guests (login/register) but user is already logged in
  if (to.meta.requiresGuest && isAuthenticated) {
    return next({ name: 'home' })
  }

  // Otherwise, allow navigation
  next()
})

export default router
