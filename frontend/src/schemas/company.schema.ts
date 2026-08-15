import { z } from "zod";

export const companySchema = z.object({
  name: z.string().min(2, "Company name is required."),
  legal_name: z.string().min(2, "Legal name is required."),
  industry: z.string().min(2, "Industry is required."),
  fiscal_year_end: z.string().min(8, "Fiscal year end is required."),
});

export const companyCreateSchema = z.object({
  name: z.string().trim().min(2, "Company name is required."),
  legal_name: z.string().trim().optional(),
  industry: z.string().trim().optional(),
  currency: z
    .string()
    .trim()
    .regex(/^[A-Za-z]{3}$/, "Currency must be a 3-letter ISO code.")
    .default("IDR"),
});

export type CompanyCreateForm = z.infer<typeof companyCreateSchema>;
