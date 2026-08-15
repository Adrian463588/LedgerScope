<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ucwords(str_replace('_', ' ', $statement->statement_type)) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 14px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .statement-title { font-size: 16px; margin: 5px 0 0 0; }
        .statement-period { font-size: 12px; color: #666; margin: 5px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; text-align: left; }
        th { border-bottom: 2px solid #ddd; font-weight: bold; }
        td { border-bottom: 1px solid #eee; }
        .amount { text-align: right; }
        .section-header { font-weight: bold; font-size: 15px; margin-top: 15px; margin-bottom: 5px; text-transform: uppercase; }
        .indent { padding-left: 25px; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .total-row td { border-top: 1px solid #333; border-bottom: 2px double #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="company-name">{{ $company->name }}</h1>
        <h2 class="statement-title">{{ ucwords(str_replace('_', ' ', $statement->statement_type)) }}</h2>
        <p class="statement-period">For the period ending {{ $statement->period?->end_date ?? 'N/A' }}</p>
    </div>

    @if($statement->statement_type === 'income_statement')
        @php
            $data = $statement->data ?? [];
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Account Code</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['revenue' => 'Revenue', 'cogs' => 'Cost of Goods Sold', 'expenses' => 'Operating Expenses', 'other_income' => 'Other Income', 'other_expenses' => 'Other Expenses'] as $key => $title)
                    @if(isset($data[$key]) && count($data[$key]['lines'] ?? []) > 0)
                        <tr>
                            <td colspan="3" class="section-header">{{ $title }}</td>
                        </tr>
                        @foreach($data[$key]['lines'] as $line)
                            <tr>
                                <td class="indent">{{ $line['account_name'] }}</td>
                                <td>{{ $line['account_code'] }}</td>
                                <td class="amount">{{ \App\Support\Decimal::format((string) $line['amount']) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2">Total {{ $title }}</td>
                            <td class="amount">{{ \App\Support\Decimal::format((string) ($data[$key]['total'] ?? '0.00')) }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr class="total-row" style="background-color: #eef2f7;">
                    <td colspan="2">NET INCOME</td>
                    <td class="amount">{{ \App\Support\Decimal::format((string) ($data['net_income'] ?? '0.00')) }}</td>
                </tr>
            </tbody>
        </table>
    @elseif($statement->statement_type === 'balance_sheet')
        @php
            $data = $statement->data ?? [];
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Account Code</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($data['assets']) && count($data['assets']['lines'] ?? []) > 0)
                    <tr>
                        <td colspan="3" class="section-header">Assets</td>
                    </tr>
                    @foreach($data['assets']['lines'] as $line)
                        <tr>
                            <td class="indent">{{ $line['account_name'] }}</td>
                            <td>{{ $line['account_code'] }}</td>
                            <td class="amount">{{ \App\Support\Decimal::format((string) $line['amount']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Assets</td>
                        <td class="amount">{{ \App\Support\Decimal::format((string) ($data['assets']['total'] ?? '0.00')) }}</td>
                    </tr>
                @endif

                @if(isset($data['liabilities_and_equity']) && count($data['liabilities_and_equity']['lines'] ?? []) > 0)
                    <tr>
                        <td colspan="3" class="section-header">Liabilities and Equity</td>
                    </tr>
                    @foreach($data['liabilities_and_equity']['lines'] as $line)
                        <tr>
                            <td class="indent">{{ $line['account_name'] }}</td>
                            <td>{{ $line['account_code'] }}</td>
                            <td class="amount">{{ \App\Support\Decimal::format((string) $line['amount']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Liabilities and Equity</td>
                        <td class="amount">{{ \App\Support\Decimal::format((string) ($data['liabilities_and_equity']['total'] ?? '0.00')) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
</body>
</html>
