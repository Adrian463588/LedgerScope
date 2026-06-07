import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';

import { usePermission } from '@/composables/usePermission';
import { useAuthStore } from '@/stores/auth.store';

describe('usePermission', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('allows wildcard demo user', () => {
    const auth = useAuthStore();
    auth.user = {
      id: 1,
      name: 'Demo User',
      email: 'demo@example.com',
      phone: null,
      avatar_path: null,
      status: 'active',
      mfa_enabled: false,
      permissions: ['*'],
      roles: [{ id: 1, name: 'admin', display_name: 'Admin' }],
    };

    const permission = usePermission();

    expect(permission.can('journal.post')).toBe(true);
    expect(permission.canAll(['journal.post', 'company.view'])).toBe(true);
  });
});

