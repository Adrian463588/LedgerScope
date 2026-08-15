import { z } from "zod";

const decimalString = z
  .string()
  .trim()
  .regex(/^-?\d+(?:[.,]\d{1,2})?$/, "Enter a valid decimal amount.");

export const reconciliationSchema = z.object({
  account_id: z.number().int().positive("Select an account."),
  accounting_period_id: z
    .number()
    .int()
    .positive("Select an accounting period."),
  reconciliation_type: z.enum(["bank", "ar", "ap"]),
  book_balance: decimalString,
  bank_balance: decimalString,
});

export type ReconciliationForm = z.infer<typeof reconciliationSchema>;
