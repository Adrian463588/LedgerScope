import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';

import { usePermission } from '@/composables/usePermission';

describe('usePermission', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('allows wildcard demo user', () => {
    const permission = usePermission();

    expect(permission.can('journal.post')).toBe(true);
    expect(permission.canAll(['journal.post', 'company.view'])).toBe(true);
  });
});
