@extends('layoutmodule::admin.main')

@section('title')
Show Salon
@endsection

@section('content')


<div class="content-header row">
    <div class="content-header-left mb-2 breadcrumb-new col">
        <h3 class="content-header-title">
                {{$salon->name}}
            </h3>
        </div>
    </div>

    @include('layoutmodule::admin.flash')

    <div class="content-body">
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-8"></div>
                            <div class="col-lg-4">
                                @if($salon->is_active == 1)
                                <a class="btn btn-grey" href="{{route('admin.salons.suspend',$salon->id)}}"
                                    role="button">Suspend</a>
                                @else
                                <a class="btn btn-green" href="{{route('admin.salons.active',$salon->id)}}"
                                    role="button">Un-suspend</a>
                                @endif


                                <a class="btn btn-warning" href="{{route('admin.salons.edit',$salon->id)}}"
                                    role="button">تعديل</a>

                                <a href="{{ route('admin.salons.delete',[$salon->id])}}"
                                    onclick="return confirm('Are you sure you want to remove this salon')"
                                    class="btn btn-danger" role="button">Remove</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="card-block">
                            <dl class="row">
                                <dt class="col-sm-12" style="text-align: center;margin-bottom: 20px">
                                    @if($salon->logo != null)
                                    <img src="{{url('uploads/salons/' . $salon->logo)}}" height="120px" />
                                    @else
                                    <img src="{{url('uploads/salons/default.jpg')}}" height="120px" />
                                    @endif
                                </dt>
                                <dt class="col-sm-3">Name</dt>
                                <dd class="col-sm-9">{{$salon->name}}</dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{$salon->email}}</dd>

                                <dt class="col-sm-3">Phone</dt>
                                <dd class="col-sm-9">{{$salon->phone}}</dd>

                                <dt class="col-sm-3">Address</dt>
                                <dd class="col-sm-9">{{$salon->address}}
                                </dd>

                                <dt class="col-sm-3">Available Accounts</dt>
                                <dd class="col-sm-9">
                                    <span class="repscount{{$salon->id}}">{{$salon->available_accounts}}</span>

                                    <a class="btn-sm btn-warning one-rep-update" href="#" id="{{$salon->id}}"
                                        role="button" data-toggle="modal"
                                        data-target="#modal-rep-update-{{$salon->id}}"><i
                                            class="fa fa-pencil"></i></a>
                                </dd>
                            </dl>
                        </div>

                        <div class="card-block">
                            <div class="pt-2">
                                <div class="row content-header">
                                    <div class="content-header-left col-md-6 col-xs-12 mb-1">
                                        <h4 class="card-title">Medical Reps ({{$salon->reps->count()}})</h4>
                                    </div>
                                    <div class="content-header-right col-md-4 col-xs-12 text-right"></div>
                                    <div class="content-header-right col-md-2 col-xs-12 text-right">

                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>&nbsp;</th>
                                                <th>Name</th>
                                                <th>Postition</th>
                                                <th class="text-center">Listed Doctors</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salon->reps as $rep)
                                            <tr>
                                                <td>
                                                    @if($rep->image != null)
                                                    <img src="{{url('uploads/reps/' . $rep->image)}}" />
                                                    @else
                                                    <img src="{{url('uploads/reps/default.jpg')}}" />
                                                    @endif
                                                </td>
                                                <td>{{$rep->name}}</td>
                                                <td>{{$rep->position}}</td>
                                                <td class="text-center">{{$rep->doctors->count()}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>




                    <div class="modal fade text-xs-left rep-update-modal" id="modal-rep-update-{{$salon->id}}"
                        tabindex="-1" role="dialog" aria-labelledby="myModalLabel90" aria-hidden="true">
                        <div class="modal-dialog" role="document" style="max-width: 400px;">
                            <div class="modal-content">
                                <div id="pay-{{$salon->id}}-info" style="">
                                    <div class="modal-body content-reports pay-modal" style="padding:0">
                                        <div class="statistic-table custom-bar">

                                            <form class="card-form" id="payActionForm">
                                                @csrf
                                                <div class="content-body">
                                                    <section class="card">
                                                        <div class="card-header">
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                            <h4>
                                                                Update Accouts
                                                            </h4>
                                                        </div>

                                                        <div class="card-block pay-template">
                                                            <div class="card-text col-md-12">
                                                                <label>
                                                                    How many accounts :
                                                                </label>
                                                                <br />
                                                                <span>
                                                                    <input type="text" id="amount{{$salon->id}}"
                                                                        size="10" name="amount" />
                                                                </span>

                                                                <a class="btn-sm btn-success updateAcc"
                                                                    id="{{$salon->id}}" href="#">Update</a>
                                                                <span class='loading' style="display: none">
                                                                    <img src="{{url('/assets/images/loading.svg')}}"
                                                                        style="border: 0;" />
                                                                </span>
                                                            </div>
                                                        </div>
                                                </div>

                                                </section>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>







                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function () {
    $(".updateAcc").on("click", function(){
        var coID = this.id ;
        var accounts = $('input#amount'+coID).val();

        $(".loading").show();
        $(".updateAcc").hide();
        $.ajax({
                type:'POST',
                url:'{{route("admin.salon.update_reps_count") }}',
                data:{
                'coID': coID,
                'accounts': accounts,
                '_token':'<?php echo csrf_token() ?>'
                },
                success:function(data){
                    setTimeout(function(){
                        $(".loading").hide();
                        $('.repscount'+coID).html(accounts);
                        $('#modal-rep-update-'+coID).modal('toggle');
                        $(".updateAcc").show();
                        $('input#amount'+coID).val("");
                    }, 2000);
                },
                error: function(data){
                    $(".loading").hide();
                    $(".updateAcc").show();
                    $('input#amount'+coID).val("");
                    // showErrorWindow('Status Not Updated');
                    // return false;
                    var errors = data.responseJSON;
                    console.log(errors);
                }
        });
        return false;
    });
});

</script>
@endpush



@endsection


@section('vendor-js')
@endsection

@section('page-level-js')
@endsection