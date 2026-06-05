import { z } from 'zod';

export const loginSchema = z.object({
  email: z.string().email('Enter a valid email address.'),
  password: z.string().min(8, 'Password must be at least 8 characters.'),
  remember: z.boolean().default(false),
});

export const mfaSchema = z.object({
  code: z.string().regex(/^\d{6}$/, 'Enter the 6-digit verification code.'),
});

export type LoginPayload = z.infer<typeof loginSchema>;
