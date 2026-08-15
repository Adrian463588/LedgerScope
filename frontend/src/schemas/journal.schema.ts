import { z } from "zod";

import { addDecimals, compareDecimals } from "@/utils/decimal";

const lineSchema = z.object({
  account_id: z.number().int().positive(),
  debit: z
    .string()
    .trim()
    .regex(/^\d+(?:[.,]\d{1,2})?$/, "Enter a valid debit amount."),
  credit: z
    .string()
    .trim()
    .regex(/^\d+(?:[.,]\d{1,2})?$/, "Enter a valid credit amount."),
  description: z.string().nullable(),
});

export const journalSchema = z
  .object({
    date: z.string().min(8, "Date is required."),
    description: z.string().min(3, "Description is required."),
    lines: z.array(lineSchema).min(2, "At least two lines are required."),
  })
  .superRefine((value, ctx) => {
    const debit = addDecimals(value.lines.map((line) => line.debit));
    const credit = addDecimals(value.lines.map((line) => line.credit));
    if (compareDecimals(debit, credit) !== 0) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Total debit must equal total credit.",
        path: ["lines"],
      });
    }
  });
