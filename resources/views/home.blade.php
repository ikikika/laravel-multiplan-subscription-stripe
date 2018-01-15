@extends('layouts.app')

@section('content')


<div class="container">
  <div class="row">
    @if (session('status'))
        <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ session('status') }}
        </div>
    @endif

  </div>

  <div class="row">
    <div class="col-sm-6">


      <form action="{{ route('payment') }}" method="post" >
        {{ csrf_field() }}

        @foreach($allplans as $plan)
          <div class="checkbox">
            <label>
              <input type="checkbox" name="plan[]" value="{{ $plan->id }}"> {{ $plan->name }} ( {{ $plan->currency }} {{ $plan->amount / 100 }} / {{ $plan->interval }} )
            </label>
          </div>
        @endforeach


        <!--
        <input type="text" name="plan[]" value="10days" />
        <input type="text" name="plan[]" value="1day" />
      -->


        <button class="btn btn-info">Make Payment</button>
      </form>


      @for($i = 0; $i < count($subscribed_plans); $i++)
      <form action="{{ route('cancelSubscription') }}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="sub_id" value="{{$subscribed_plans[$i]->id}}" />
        <button class="btn btn-danger">Cancel {{ $subscribed_plans[$i]->plan->name }}</button>
      </form>

      @endfor
    </div>

  </div>
</div>



@endsection
