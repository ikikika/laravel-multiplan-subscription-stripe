@extends('layouts.app')

@section('css')
<style>

div label input {
   margin-right:100px;
}
body {
    font-family:sans-serif;
}

#ck-button {
    margin:4px;
    background-color:#EFEFEF;
    border-radius:4px;
    border:1px solid #D0D0D0;
    overflow:auto;
    float:left;
}

#ck-button label {
    float:left;
    width:4.0em;
    padding:0px;
}

#ck-button label span {
    text-align:center;
    padding:3px 0px;
    display:block;
}

#ck-button label input {
    position:absolute;
    top:-20px;
}

#ck-button input:checked + span {
    background-color:#911;
    color:#fff;
}

</style>

@endsection

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
      <table class="table">
        <thead>
          <th>Plan</th>
          <th>Action</th>
        </thead>
        <tbody>
          @foreach($allplans as $plan)
          <tr>
            <td>{{ $plan->name }} ( {{ $plan->currency }} {{ $plan->amount / 100 }} / {{ $plan->interval }} )</td>
            <td>
              <div class="checkbox">
                  @if( !in_array( $plan->name, $subscribed_plan_names ) )
                  <div id="ck-button">
                     <label>
                        <input type="checkbox" name="plan[]" value="{{ $plan->id }}" hidden><span>Select</span>
                     </label>
                  </div>
                  @else
                  <div class="btn btn-default disabled">Subscribed</div>
                  @endif
              </div>

            </td>
          </tr>

          @endforeach

        </tbody>
      </table>



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
                  <input type="hidden" name="sub_id" value="{{$subscribed_plans[$i]->stripe_id}}" />
                  <input type="hidden" name="db_id" value="{{$subscribed_plans[$i]->db_id}}" />
                  <button class="btn btn-danger">Cancel </button>
                </form>
              @else
              <form action="{{ route('subscriptionResume') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="sub_item_id" value="{{$subscribed_plans[$i]->sub_item_id}}" />
                <input type="hidden" name="sub_id" value="{{$subscribed_plans[$i]->stripe_id}}" />
                <input type="hidden" name="db_id" value="{{$subscribed_plans[$i]->db_id}}" />
                <input type="hidden" name="plan_name" value="{{$subscribed_plans[$i]->stripe_plan}}" />
                <button class="btn btn-info">Resume </button>
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

@section('scripts')
<script>
function yesno(thecheckbox, thelabel) {

    var checkboxvar = document.getElementById(thecheckbox);
    var labelvar = document.getElementById(thelabel);
    if (!checkboxvar.checked) {
        labelvar.innerHTML = "No";
    }
    else {
        labelvar.innerHTML = "Yes";
    }
}
</script>
@endsection
