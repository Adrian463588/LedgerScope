import { z } from 'zod';

export const engagementSchema = z.object({
  name: z.string().min(3, 'Engagement name is required.'),
  type: z.enum(['audit', 'review', 'compilation', 'tax']),
  period: z.string().min(2, 'Period is required.'),
});
