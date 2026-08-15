<script setup lang="ts">
import { Edit, Plus } from "lucide-vue-next";
import { computed, reactive, ref, watch } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";
import type { CompanyUpdatePayload } from "@/api/endpoints";

const { companyApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppModal from "@/components/ui/AppModal.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { router } from "@/router";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { Company, CompanyContact } from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const company = ref<Company | null>(null);
const contacts = ref<CompanyContact[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);
const isEditOpen = ref(false);
const isContactOpen = ref(false);
const isSaving = ref(false);
const formError = ref<string | null>(null);

const companyId = computed(() => {
  const rawId = router.currentRoute.value.params["id"];
  const parsed = Number(rawId);
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
});

const editForm = reactive({
  name: "",
  legal_name: "",
  industry: "",
  currency: "",
  address: "",
  city: "",
  country: "",
  phone: "",
  email: "",
  website: "",
});

const contactForm = reactive({
  name: "",
  email: "",
  phone: "",
  role: "",
});

function syncEditForm(value: Company): void {
  editForm.name = value.name;
  editForm.legal_name = value.legal_name;
  editForm.industry = value.industry ?? "";
  editForm.currency = value.currency ?? "";
  editForm.address = value.address ?? "";
  editForm.city = value.city ?? "";
  editForm.country = value.country ?? "";
  editForm.phone = value.phone ?? "";
  editForm.email = value.email ?? "";
  editForm.website = value.website ?? "";
}

async function load(): Promise<void> {
  if (!companyId.value) {
    error.value = "A valid company is required.";
    isLoading.value = false;
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    const [loadedCompany, loadedContacts] = await Promise.all([
      companyApi.get(companyId.value),
      companyApi.contacts(companyId.value),
    ]);
    company.value = loadedCompany;
    contacts.value = loadedContacts;
    syncEditForm(loadedCompany);
    companies.switchCompany(loadedCompany.id);
    ui.setBreadcrumbs(["Companies", loadedCompany.name]);
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load company.";
  } finally {
    isLoading.value = false;
  }
}

function openEdit(): void {
  if (company.value) syncEditForm(company.value);
  formError.value = null;
  isEditOpen.value = true;
}

async function saveCompany(): Promise<void> {
  if (!companyId.value || !editForm.name.trim()) {
    formError.value = "Company name is required.";
    return;
  }

  isSaving.value = true;
  formError.value = null;
  const payload: CompanyUpdatePayload = {
    name: editForm.name.trim(),
    legal_name: editForm.legal_name.trim() || undefined,
    industry: editForm.industry.trim() || undefined,
    currency: editForm.currency.trim() || undefined,
    address: editForm.address.trim() || undefined,
    city: editForm.city.trim() || undefined,
    country: editForm.country.trim() || undefined,
    phone: editForm.phone.trim() || undefined,
    email: editForm.email.trim() || undefined,
    website: editForm.website.trim() || undefined,
  };

  try {
    company.value = await companyApi.update(companyId.value, payload);
    await companies.fetchCompanies();
    companies.switchCompany(companyId.value);
    isEditOpen.value = false;
  } catch (caught) {
    formError.value =
      caught instanceof Error ? caught.message : "Unable to update company.";
  } finally {
    isSaving.value = false;
  }
}

function resetContactForm(): void {
  contactForm.name = "";
  contactForm.email = "";
  contactForm.phone = "";
  contactForm.role = "";
  formError.value = null;
}

function openContact(): void {
  resetContactForm();
  isContactOpen.value = true;
}

async function addContact(): Promise<void> {
  if (!companyId.value || !contactForm.name.trim()) {
    formError.value = "Contact name is required.";
    return;
  }

  isSaving.value = true;
  formError.value = null;
  try {
    const contact = await companyApi.addContact(companyId.value, {
      name: contactForm.name.trim(),
      email: contactForm.email.trim() || undefined,
      phone: contactForm.phone.trim() || undefined,
      role: contactForm.role.trim() || undefined,
    });
    contacts.value = [...contacts.value, contact];
    isContactOpen.value = false;
    resetContactForm();
  } catch (caught) {
    formError.value =
      caught instanceof Error ? caught.message : "Unable to add contact.";
  } finally {
    isSaving.value = false;
  }
}

watch(companyId, () => void load(), { immediate: true });
</script>

<template>
  <div v-if="isLoading" class="state">Loading company profile...</div>
  <div v-else-if="error" class="state state--error">{{ error }}</div>
  <template v-else-if="company">
    <PageHeader
      :title="company.name"
      subtitle="Entity profile, contacts, and audit history."
    >
      <template #actions>
        <StatusBadge :status="company.status" />
        <AppButton :icon="Edit" @click="openEdit">Edit Profile</AppButton>
      </template>
    </PageHeader>

    <section class="profile-grid">
      <SectionPanel title="Company Details">
        <dl>
          <div>
            <dt>Legal Name</dt>
            <dd>{{ company.legal_name }}</dd>
          </div>
          <div>
            <dt>Industry</dt>
            <dd>{{ company.industry || "—" }}</dd>
          </div>
          <div>
            <dt>Registration Number</dt>
            <dd>{{ company.registration_number || "—" }}</dd>
          </div>
          <div>
            <dt>Tax ID</dt>
            <dd>{{ company.tax_id || "—" }}</dd>
          </div>
          <div>
            <dt>Currency</dt>
            <dd>{{ company.currency || "—" }}</dd>
          </div>
          <div>
            <dt>Status</dt>
            <dd><StatusBadge :status="company.status" /></dd>
          </div>
        </dl>
      </SectionPanel>
      <SectionPanel title="Contact & Address">
        <dl>
          <div>
            <dt>Address</dt>
            <dd>{{ company.address || "—" }}</dd>
          </div>
          <div>
            <dt>Location</dt>
            <dd>
              {{
                [company.city, company.country].filter(Boolean).join(", ") ||
                "—"
              }}
            </dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{{ company.email || "—" }}</dd>
          </div>
          <div>
            <dt>Phone</dt>
            <dd>{{ company.phone || "—" }}</dd>
          </div>
          <div>
            <dt>Website</dt>
            <dd>{{ company.website || "—" }}</dd>
          </div>
        </dl>
      </SectionPanel>
    </section>

    <SectionPanel title="Company Contacts">
      <template #actions>
        <AppButton :icon="Plus" @click="openContact"> Add contact </AppButton>
      </template>
      <div v-if="contacts.length === 0" class="empty">
        No contacts configured.
      </div>
      <div v-else class="contacts-list">
        <article
          v-for="contact in contacts"
          :key="contact.id"
          class="contact-card"
        >
          <strong>{{ contact.name }}</strong>
          <span>{{ contact.role || "Contact" }}</span>
          <span>{{
            contact.email || contact.phone || "No contact details"
          }}</span>
        </article>
      </div>
    </SectionPanel>
  </template>

  <AppModal :open="isEditOpen" title="Edit company" @close="isEditOpen = false">
    <div class="form-grid">
      <AppInput v-model="editForm.name" label="Company name" required />
      <AppInput v-model="editForm.legal_name" label="Legal name" />
      <AppInput v-model="editForm.industry" label="Industry" />
      <AppInput v-model="editForm.currency" label="Currency" />
      <AppInput v-model="editForm.address" label="Address" />
      <AppInput v-model="editForm.city" label="City" />
      <AppInput v-model="editForm.country" label="Country" />
      <AppInput v-model="editForm.phone" label="Phone" />
      <AppInput v-model="editForm.email" label="Email" type="email" />
      <AppInput v-model="editForm.website" label="Website" type="url" />
    </div>
    <p v-if="formError" class="state state--error">{{ formError }}</p>
    <template #footer>
      <AppButton @click="isEditOpen = false">Cancel</AppButton>
      <AppButton variant="primary" :loading="isSaving" @click="saveCompany"
        >Save changes</AppButton
      >
    </template>
  </AppModal>

  <AppModal
    :open="isContactOpen"
    title="Add contact"
    @close="isContactOpen = false"
  >
    <div class="form-grid">
      <AppInput v-model="contactForm.name" label="Name" required />
      <AppInput v-model="contactForm.role" label="Role" />
      <AppInput v-model="contactForm.email" label="Email" type="email" />
      <AppInput v-model="contactForm.phone" label="Phone" />
    </div>
    <p v-if="formError" class="state state--error">{{ formError }}</p>
    <template #footer>
      <AppButton @click="isContactOpen = false">Cancel</AppButton>
      <AppButton variant="primary" :loading="isSaving" @click="addContact"
        >Add contact</AppButton
      >
    </template>
  </AppModal>
</template>

<style scoped>
.profile-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

dl {
  display: grid;
  gap: 16px;
  margin: 0;
}

dl > div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

dt {
  color: var(--text-muted);
}

dd {
  margin: 0;
  color: var(--text-primary);
  font-weight: 600;
  text-align: right;
}

.contacts-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.contact-card {
  display: grid;
  gap: 4px;
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 6px;
}

.contact-card span {
  color: var(--text-secondary);
  font-size: 0.8125rem;
}

.empty,
.state {
  padding: 24px;
  color: var(--text-secondary);
}

.state--error {
  color: var(--status-danger);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 800px) {
  .profile-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  dl > div {
    align-items: flex-start;
    flex-direction: column;
    gap: 4px;
  }

  dd {
    text-align: left;
  }
}
</style>
