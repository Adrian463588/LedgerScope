import { z } from 'zod';

export const companySchema = z.object({
  name: z.string().min(2, 'Company name is required.'),
  legal_name: z.string().min(2, 'Legal name is required.'),
  industry: z.string().min(2, 'Industry is required.'),
  fiscal_year_end: z.string().min(8, 'Fiscal year end is required.'),
});
