<template>
  <div>
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            فرم ساز
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <button class="btn btn-primary" @click="saveForm">
            ذخیره فرم
          </button>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="card mb-4">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">نام فرم</label>
            <input type="text" class="form-control" v-model="form.name">
          </div>
          <div class="mb-3">
            <label class="form-label">توضیحات فرم</label>
            <textarea class="form-control" v-model="form.description"></textarea>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">فیلدهای فرم</h3>
          <div class="ms-auto">
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                افزودن فیلد جدید
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" @click.prevent="addField('text')">ورودی متنی</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="addField('textarea')">ناحیه متنی</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="addField('number')">ورودی عددی</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="addField('date')">ورودی تاریخ</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div v-if="!form.fields || form.fields.length === 0" class="text-center text-muted">
            هیچ فیلدی وجود ندارد. یک فیلد جدید از منوی بالا اضافه کنید.
          </div>
          <draggable v-model="form.fields" item-key="id" class="list-group" handle=".handle">
            <template #item="{ element: field, index }">
              <div class="list-group-item">
                <div class="row align-items-center">
                  <div class="col-auto handle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 9l-3 3l3 3"></path><path d="M9 5l3 -3l3 3"></path><path d="M15 19l3 3l3 -3"></path><path d="M19 9l3 3l-3 3"></path><path d="M5 15l3 0"></path><path d="M19 15l-3 0"></path><path d="M9 5l0 3"></path><path d="M15 5l0 3"></path><path d="M9 19l0 -3"></path><path d="M15 19l0 -3"></path></svg>
                  </div>
                  <div class="col">
                    <div class="row">
                      <div class="col-md-4 mb-2">
                        <label class="form-label">برچسب (Label)</label>
                        <input type="text" class="form-control" v-model="field.label" placeholder="e.g., نام محصول">
                      </div>
                      <div class="col-md-4 mb-2">
                        <label class="form-label">نام متغیر (Name)</label>
                        <input type="text" class="form-control" v-model="field.name" placeholder="e.g., product_name">
                      </div>
                      <div class="col-md-4 mb-2">
                        <label class="form-label">نوع فیلد</label>
                        <select class="form-select" v-model="field.type">
                          <option value="text">متنی</option>
                          <option value="textarea">ناحیه متنی</option>
                          <option value="number">عددی</option>
                          <option value="date">تاریخ</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" v-model="field.is_required">
                        <label class="form-check-label">ضروری</label>
                    </div>
                    <button class="btn btn-danger btn-sm" @click="removeField(index)">حذف</button>
                  </div>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/services/api';
import draggable from 'vuedraggable';

const props = defineProps({
  id: { type: String, required: true },
});

const router = useRouter();
const form = ref({ name: '', description: '', fields: [] });
const loading = ref(true);

const fetchForm = async () => {
  loading.value = true;
  try {
    const response = await apiClient.get(`/forms.php?id=${props.id}`);
    form.value = response.data;
    if (!form.value.fields) {
        form.value.fields = [];
    }
  } catch (err) {
    console.error('Failed to fetch form', err);
    alert('Failed to load form data.');
  } finally {
    loading.value = false;
  }
};

const addField = (type) => {
  const newField = {
    // Use a temporary negative ID for new fields for the key prop
    id: `new_${Date.now()}`,
    label: 'فیلد جدید',
    name: 'new_field',
    type: type,
    is_required: false,
    options: null,
  };
  form.value.fields.push(newField);
};

const removeField = (index) => {
  if (confirm('آیا از حذف این فیلد اطمینان دارید؟')) {
    form.value.fields.splice(index, 1);
  }
};

const saveForm = async () => {
    loading.value = true;
    // Before saving, remove temporary IDs from new fields
    const payload = JSON.parse(JSON.stringify(form.value));
    payload.fields.forEach(field => {
        if (String(field.id).startsWith('new_')) {
            delete field.id;
        }
    });

    try {
        await apiClient.put(`/forms.php?id=${props.id}`, payload);
        alert('فرم با موفقیت ذخیره شد.');
        router.push({ name: 'admin-forms' });
    } catch (err) {
        console.error('Failed to save form', err);
        alert('خطا در ذخیره سازی فرم.');
    } finally {
        loading.value = false;
    }
};

onMounted(fetchForm);
</script>

<style scoped>
.handle {
  cursor: move;
}
.list-group-item {
    margin-bottom: 10px;
    border-radius: 5px;
}
</style>
