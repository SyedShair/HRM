<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="ui icon user circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text uppercase">{{ __('Employees') }}</span>
                <div class="progress-group">
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-aqua" style="width: 100%"></div>
                    </div>
                    <div class="stats_d">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>{{ __('Regular') }}</td>
                                    <td>{{ $emp_typeR ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Part Time') }}</td>
                                    <td>{{ $emp_typeT ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ui icon clock outline"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Attendances') }}</span>
                <div class="progress-group">
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-green" style="width: 100%"></div>
                    </div>
                    <div class="stats_d">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>{{ __('Online') }}</td>
                                    <td>{{ $is_online_now ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Offline') }}</td>
                                    <td>{{ $is_offline_now ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="info-box">
            <span class="info-box-icon bg-orange"><i class="ui icon home"></i></span>
            <div class="info-box-content">
                <span class="info-box-text uppercase">{{ __('Leaves of Absence') }}</span>
                <div class="progress-group">
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-orange" style="width: 100%"></div>
                    </div>
                    <div class="stats_d">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td>{{ __('Approved') }}</td>
                                    <td>{{ $emp_leaves_approve ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Pending') }}</td>
                                    <td>{{ $emp_leaves_pending ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>