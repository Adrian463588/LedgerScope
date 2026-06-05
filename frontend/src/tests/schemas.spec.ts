import { describe, expect, it } from 'vitest';

import { loginSchema, mfaSchema } from '@/schemas/auth.schema';
import { journalSchema } from '@/schemas/journal.schema';

describe('schemas', () => {
  it('validates login and MFA inputs', () => {
    expect(loginSchema.safeParse({ email: 'bad', password: 'short', remember: false }).success).toBe(false);
    expect(mfaSchema.safeParse({ code: '123456' }).success).toBe(true);
  });

  it('requires balanced journal lines', () => {
    const result = journalSchema.safeParse({
      date: '2026-01-01',
      description: 'Accrual',
      lines: [
        { account_id: 1, debit: '100.00', credit: '0.00', description: null },
        { account_id: 2, debit: '0.00', credit: '90.00', description: null },
      ],
    });

    expect(result.success).toBe(false);
  });
});
