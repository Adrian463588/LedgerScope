<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;

/**
 * JournalRedFlagService — Epic 7D PRD §Journal Red-Flag Testing
 *
 * Implements 5 configurable red-flag rules against a set of journal entries.
 * Each rule returns a structured finding array or null if no flag raised.
 */
final class JournalRedFlagService
{
    /** Amount above which an entry is considered "large" (configurable via env) */
    private string $largeEntryThreshold;

    /** Percentage below a round number that triggers the near-threshold rule */
    private string $nearThresholdPercent;

    public function __construct()
    {
        $this->largeEntryThreshold = (string) config('ledgerscope.red_flag.large_entry_threshold', '100000');
        $this->nearThresholdPercent = (string) config('ledgerscope.red_flag.near_threshold_percent', '0.02');
    }

    /**
     * Run all rules against all journal entries for a company/period.
     *
     * @param  Collection<int, JournalEntry>  $journals
     * @return array<int, array<string, mixed>>
     */
    public function scan(Collection $journals): array
    {
        $flags = [];

        foreach ($journals as $journal) {
            $journal->loadMissing('lines');

            $this->checkWeekendOrHoliday($journal, $flags);
            $this->checkRoundNumbers($journal, $flags);
            $this->checkLargeEntries($journal, $flags);
            $this->checkNearThreshold($journal, $flags);
            $this->checkUnusualAccountCombination($journal, $flags);
        }

        return $flags;
    }

    // ─── Rule 1: Weekend / public-holiday posting ──────────────────────────────

    /** @param  array<int, array<string, mixed>>  $flags */
    private function checkWeekendOrHoliday(JournalEntry $journal, array &$flags): void
    {
        $dayOfWeek = $journal->journal_date->dayOfWeek;

        if (in_array($dayOfWeek, [0, 6], true)) {  // 0=Sunday, 6=Saturday
            $flags[] = $this->flag(
                $journal,
                'weekend_posting',
                'Journal posted on a weekend',
                ['day_of_week' => $journal->journal_date->format('l')],
            );
        }
    }

    // ─── Rule 2: Round-number amounts ─────────────────────────────────────────

    /** @param  array<int, array<string, mixed>>  $flags */
    private function checkRoundNumbers(JournalEntry $journal, array &$flags): void
    {
        foreach ($journal->lines as $line) {
            $amount = $this->absoluteAmount((string) $line->amount);

            // Flag if amount is >= 1000 and divisible by 1000 with no cents
            if (bccomp($amount, '1000', 2) >= 0 && bccomp(bcmod($amount, '1000', 2), '0', 2) === 0) {
                $flags[] = $this->flag(
                    $journal,
                    'round_number_entry',
                    'Journal line contains a suspiciously round amount',
                    ['line_id' => $line->id, 'amount' => $amount],
                );
                break;  // one flag per journal
            }
        }
    }

    // ─── Rule 3: Large entries ────────────────────────────────────────────────

    /** @param  array<int, array<string, mixed>>  $flags */
    private function checkLargeEntries(JournalEntry $journal, array &$flags): void
    {
        foreach ($journal->lines as $line) {
            $amount = $this->absoluteAmount((string) $line->amount);

            if (bccomp($amount, $this->largeEntryThreshold, 2) >= 0) {
                $flags[] = $this->flag(
                    $journal,
                    'large_entry',
                    "Journal line exceeds large-entry threshold ({$this->largeEntryThreshold})",
                    ['line_id' => $line->id, 'amount' => $amount, 'threshold' => $this->largeEntryThreshold],
                );
                break;
            }
        }
    }

    // ─── Rule 4: Near-threshold amounts (just below a round number) ───────────

    /** @param  array<int, array<string, mixed>>  $flags */
    private function checkNearThreshold(JournalEntry $journal, array &$flags): void
    {
        $roundTargets = ['1000', '5000', '10000', '50000', '100000', '500000', '1000000'];

        foreach ($journal->lines as $line) {
            $amount = $this->absoluteAmount((string) $line->amount);

            foreach ($roundTargets as $target) {
                $lower = bcmul(
                    $target,
                    bcsub('1', $this->nearThresholdPercent, 8),
                    2,
                );

                if (bccomp($amount, $lower, 2) >= 0 && bccomp($amount, $target, 2) < 0) {
                    $flags[] = $this->flag(
                        $journal,
                        'near_threshold_amount',
                        "Journal line amount is just below a key threshold ({$target})",
                        ['line_id' => $line->id, 'amount' => $amount, 'threshold' => $target],
                    );
                    break 2;  // one flag per journal
                }
            }
        }
    }

    // ─── Rule 5: Unusual account combinations (debit/credit same type) ────────

    /** @param  array<int, array<string, mixed>>  $flags */
    private function checkUnusualAccountCombination(JournalEntry $journal, array &$flags): void
    {
        // Flag entries that only touch accounts of the same normal-balance type
        // (e.g. only asset accounts on both sides — likely an internal transfer mismatch)
        $journal->lines->loadMissing('account');

        $accountTypes = $journal->lines
            ->filter(static fn (JournalEntryLine $line): bool => $line->account !== null)
            ->map(static fn (JournalEntryLine $line): string => (string) $line->account->account_type)
            ->unique()
            ->values();

        if ($accountTypes->count() === 1) {
            $flags[] = $this->flag(
                $journal,
                'unusual_account_combination',
                "All journal lines touch accounts of the same type ({$accountTypes->first()})",
                ['account_type' => $accountTypes->first()],
            );
        }
    }

    // ─── Shared builder ───────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function flag(JournalEntry $journal, string $rule, string $message, array $context = []): array
    {
        return [
            'journal_id' => $journal->id,
            'journal_number' => $journal->journal_number,
            'journal_date' => $journal->journal_date->toDateString(),
            'rule' => $rule,
            'message' => $message,
            'context' => $context,
        ];
    }

    private function absoluteAmount(string $amount): string
    {
        $normalised = bcadd($amount, '0', 2);

        return bccomp($normalised, '0', 2) < 0
            ? bcmul($normalised, '-1', 2)
            : $normalised;
    }
}
