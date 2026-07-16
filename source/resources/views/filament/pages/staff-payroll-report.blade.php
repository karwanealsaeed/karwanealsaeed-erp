<x-filament-panels::page>
    @php
        $rows = $this->rows();
        $staffMembers = $this->staffMembers();

        $totalNet = $rows->sum('net_salary');
        $totalPaid = $rows->sum('paid_amount');
        $totalBalance = $rows->sum('balance_amount');
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Staff Payroll Report</h2>
                <p>Monthly salary, paid amount and balance report.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <form method="GET" class="kes-report-filters">
            <select name="staff_member_id">
                <option value="">All Staff</option>
                @foreach ($staffMembers as $staff)
                    <option value="{{ $staff->id }}" @selected((int) request('staff_member_id') === $staff->id)>
                        {{ $staff->display_name }}
                    </option>
                @endforeach
            </select>

            <select name="salary_month">
                <option value="">All Months</option>
                @foreach ([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $monthNo => $monthName)
                    <option value="{{ $monthNo }}" @selected((int) request('salary_month') === $monthNo)>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>

            <input type="number" name="salary_year" value="{{ request('salary_year') }}" placeholder="Year">

            <select name="status">
                <option value="">All Status</option>
                <option value="Unpaid" @selected(request('status') === 'Unpaid')>Unpaid</option>
                <option value="Partial" @selected(request('status') === 'Partial')>Partial</option>
                <option value="Paid" @selected(request('status') === 'Paid')>Paid</option>
                <option value="Cancelled" @selected(request('status') === 'Cancelled')>Cancelled</option>
            </select>

            <button type="submit">Apply</button>
            <a href="{{ url()->current() }}">Reset</a>
        </form>

        <div class="kes-report-summary-grid">
            <div class="kes-report-summary-card">
                <span>Total Net Salary</span>
                <strong>PKR {{ number_format((float) $totalNet, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Paid</span>
                <strong>PKR {{ number_format((float) $totalPaid, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Balance</span>
                <strong>PKR {{ number_format((float) $totalBalance, 2) }}</strong>
            </div>
        </div>

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Payroll No</th>
                        <th>Employee</th>
                        <th>Month</th>
                        <th class="right">Basic</th>
                        <th class="right">Net</th>
                        <th class="right">Paid</th>
                        <th class="right">Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->payroll_no }}</td>
                            <td>{{ $row->staffMember?->display_name ?? '-' }}</td>
                            <td>{{ $row->period_text }}</td>
                            <td class="right">PKR {{ number_format((float) $row->basic_salary, 2) }}</td>
                            <td class="right">PKR {{ number_format((float) $row->net_salary, 2) }}</td>
                            <td class="right">PKR {{ number_format((float) $row->paid_amount, 2) }}</td>
                            <td class="right">PKR {{ number_format((float) $row->balance_amount, 2) }}</td>
                            <td>{{ $row->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No payroll records found.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="4">Total</th>
                        <th class="right">PKR {{ number_format((float) $totalNet, 2) }}</th>
                        <th class="right">PKR {{ number_format((float) $totalPaid, 2) }}</th>
                        <th class="right">PKR {{ number_format((float) $totalBalance, 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>