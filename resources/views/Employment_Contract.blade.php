<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employment Particulars</title>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        line-height: 1.7;
        background-color: #f5f6fa;
        color: #333;
        padding: 20px;
    }

    .box {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    }

    .title {
        text-align: center;
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 30px;
        text-transform: uppercase;
    }

    .section {
        margin-top: 30px;
        font-weight: bold;
        background: #0984e3;
        color: #fff;
        padding: 8px 12px;
        border-radius: 5px;
    }

    p { margin: 6px 0; }

    ul { margin: 10px 0 10px 20px; }

    .signature {
        margin-top: 40px;
    }

    .line {
        border-bottom: 1px solid #000;
        margin-top: 20px;
        width: 250px;
    }
</style>
</head>

<body>

<div class="box">

<div class="title">Employment Particulars</div>

<p>
This written statement lists the terms and conditions of your employment as of 
<strong>{{ $job->startdate }}</strong> in accordance with the Employment Rights Act 1996.
</p>

<div class="section">1. Employer and Employee</div>
<p><strong>Employer:</strong> {{ $company->company }}</p>
<p><strong>Employee:</strong> {{ $employee->firstname }} {{ $employee->lastname }}</p>

<div class="section">2. Start Date & Continuous Employment</div>
<p>Your employment begins on <strong>{{ $job->startdate }}</strong>.</p>
<p>Previous employment does not count as continuous employment.</p>

<div class="section">3. Job Title & Duties</div>
<p><strong>Job Title:</strong> {{ $job->jobposition }}</p>
<p><strong>Duties:</strong></p>
<p>{!! $job->jobduties !!}</p>

<div class="section">4. Pay</div>
<p>Annual Salary: <strong>£{{ $annualSalary }}</strong></p>
<p>Payment Frequency: Monthly</p>

<div class="section">5. Place of Work</div>
<p>{{ $company->address }}</p>

<div class="section">6. Working Hours</div>
<p>
{{ $schedule->hours }} hours per week  
</p>

<div class="section">7. Holiday Entitlement</div>
<p>{{ $leaveGroup->description ?? '20 days + bank holidays' }}</p>

<div class="section">8. Other Benefits</div>
<p>No additional benefits unless stated otherwise.</p>

<div class="section">9. Absence & Sick Pay</div>
<p>You must notify your manager within 20 minutes of your start time if absent.</p>
<p>Up to 7 days: self-certification required.</p>
<p>7+ days: doctor’s fit note required.</p>
<p>Statutory Sick Pay applies.</p>

<div class="section">10. Other Paid Leave</div>
<ul>
    <li>Maternity Leave</li>
    <li>Paternity Leave</li>
    <li>Adoption Leave</li>
    <li>Shared Parental Leave</li>
    <li>Bereavement Leave</li>
</ul>

<div class="section">11. Pension</div>
<p>
You will be auto-enrolled into a pension scheme if eligible under the Pensions Act 2008.

</p>



<div class="section">12. Training</div>
<p>Training may be provided (internal or external).</p>

<div class="section">13. Probation</div>
<p>Probation period: 3 months.</p>

<div class="section">14. Notice Period</div>
<p>Employee notice: 4 weeks</p>
<p>Employer notice: 4 weeks or statutory minimum (whichever is longer)</p>

<div class="section">15. Collective Agreements</div>
<p>No collective agreements apply.</p>

<div class="section">16. Grievance Procedure</div>
<p>Submit grievances in writing to your manager.</p>

<div class="section">17. Disciplinary Procedure</div>
<p>You may appeal disciplinary decisions in writing.</p>

<div class="section">18. Right to Work (UK Immigration)</div>
<p>
Employment is conditional upon your legal right to work in the UK.
You must:
</p>
<ul>
    <li>Maintain valid immigration status</li>
    <li>Provide updated documents when required</li>
    <li>Comply with visa conditions</li>
</ul>

<p>
The employer will report changes to the Home Office if required.
</p>

<div class="section">Signatures</div>

<div class="signature">
    <p>Employee Signature:</p>
    <div class="line"></div>
    <p>Date: __________</p>

    <br>

    <p>Employer Signature:</p>
    <div class="line"></div>
    <p>Date: __________</p>
</div>

</div>

</body>
</html>