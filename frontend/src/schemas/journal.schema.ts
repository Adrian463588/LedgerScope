import { z } from 'zod';

const lineSchema = z.object({
  account_id: z.number().int().positive(),
  debit: z.string(),
  credit: z.string(),
  description: z.string().nullable(),
});

export const journalSchema = z
  .object({
    date: z.string().min(8, 'Date is required.'),
    description: z.string().min(3, 'Description is required.'),
    lines: z.array(lineSchema).min(2, 'At least two lines are required.'),
  })
  .superRefine((value, ctx) => {
    const debit = value.lines.reduce((total, line) => total + Number(line.debit), 0);
    const credit = value.lines.reduce((total, line) => total + Number(line.credit), 0);
    if (debit !== credit) {
      ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Total debit must equal total credit.', path: ['lines'] });
    }
  });
