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


      <table class="table table-striped table-bordered">
        <thead class="info">
          <th>Plan</th>
          <th>End Date</th>
          <th>Action</th>
        </thead>
        <tbody>
          @for($i = 0; $i < count($subscribed_plans); $i++)
          <tr>
            <td>
              {{ $subscribed_plans[$i]->stripe_plan }}
            </td>
            <td>
              {{ $subscribed_plans[$i]->ends_at }}
            </td>
            <td>
              @if( !$subscribed_plans[$i]->ends_at )
                <form action="{{ route('cancelSubscription') }}" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" name="sub_item_id" value="{{$subscribed_plans[$i]->sub_item_id}}" />
                  <input type="hidden" name="sub_id" value="{{$subscribed_plans[0]->stripe_id}}" />
                  <button class="btn btn-danger">Cancel </button>
                </form>
              @endif


            </td>
          </tr>
          @endfor
        </tbody>
      </table>

    </div>

  </div>
</div>



@endsection
