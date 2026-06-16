@extends('layouts/contentLayoutMaster')
@section('title',"Lista Bonificación Inicial")
@section('vendor-style')
@endsection
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
      

        <div class="card-body">
            <div class="table-responsive">
            <x-table-component  tableclass="table table-hover-animation table-striped table-bordered" :datatable=true >
                <x-slot name="theadRows">
                    <tr>
                        <th>{{__('locale.User')}}</th>
                        <th>{{__('locale.starting_bonus')}}</th>
                        <th>{{__('locale.date')}}</th>
                    </tr>
                </x-slot>
                <x-slot name="tbodyRows">
                    @foreach ($qualifieds as $qualified )
                    <tr>
                        <td>{{ $qualified->user->fullName }}</td>
                        <td> <span class="text-primary font-weight-bold">$ {{ $qualified->starting_bonus }}</span></td>
                        <td>{{ $qualified->created_at->toFormattedDateString() }}</td>
                    </tr>
                    @endforeach
                </x-slot>
            </x-table-component>
          </div>



        </div>
      </div> 
     </div>  
    
  </div>
@endsection
@section('vendor-script')
@endsection
