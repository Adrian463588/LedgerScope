import { z } from 'zod';

export const evidenceSchema = z.object({
  comment: z.string().max(500, 'Comment must be 500 characters or less.').optional(),
  fileName: z.string().min(1, 'File is required.'),
});
