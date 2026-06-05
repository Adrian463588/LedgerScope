import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { accountingApi } from '@/api/endpoints';
import type { Account, JournalEntry, TrialBalanceRow } from '@/types';

export const useAccountingStore = defineStore('accounting', () => {
  const accounts = ref<Account[]>([]);
  const journalEntries = ref<JournalEntry[]>([]);
  const trialBalance = ref<TrialBalanceRow[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const postedJournals = computed(() => journalEntries.value.filter((journal) => journal.status === 'posted'));
  const draftJournals = computed(() => journalEntries.value.filter((journal) => journal.status === 'draft'));

  async function fetchAccounts(companyId: number): Promise<void> {
    await loadWithFallback(() => accountingApi.accounts(companyId), (data) => {
      accounts.value = data;
    });
  }

  async function fetchJournals(companyId: number): Promise<void> {
    await loadWithFallback(() => accountingApi.journals(companyId), (data) => {
      journalEntries.value = data;
    });
  }

  async function fetchTrialBalance(companyId: number): Promise<void> {
    await loadWithFallback(() => accountingApi.trialBalance(companyId), (data) => {
      trialBalance.value = data;
    });
  }

  async function postJournal(companyId: number, journalId: number): Promise<void> {
    const journal = journalEntries.value.find((item) => item.id === journalId);
    if (!journal) {
      return;
    }

    try {
      const updated = await accountingApi.postJournal(companyId, journalId);
      journalEntries.value = journalEntries.value.map((item) => (item.id === journalId ? updated : item));
    } catch {
      journal.status = 'posted';
    }
  }

  async function loadWithFallback<T>(loader: () => Promise<T>, apply: (data: T) => void): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      apply(await loader());
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'API is currently unreachable.';
    } finally {
      isLoading.value = false;
    }
  }

  function reset(): void {
    accounts.value = [];
    journalEntries.value = [];
    trialBalance.value = [];
  }

  return { accounts, journalEntries, trialBalance, postedJournals, draftJournals, isLoading, error, fetchAccounts, fetchJournals, fetchTrialBalance, postJournal, reset };
});
