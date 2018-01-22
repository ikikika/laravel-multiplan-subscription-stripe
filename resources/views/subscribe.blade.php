@extends('layouts.app')



@section('css')


<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
.StripeElement {
  background-color: white;
  height: 40px;
  padding: 10px 12px;
  border-radius: 4px;
  border: 1px solid #ccd0d2;
  box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
#card-errors {
  color: #fa755a;
}


</style>
@endsection


@section('content')
<div class="container">
  <div class="row">
    <div class="col-sm-6">
       Total: SGD {{ $cost }}


      <form action="{{ route('subscribe') }}" method="POST" id="payment-form" >

        {{ csrf_field() }}

        @foreach( $selected_plans as $plan )
          <input type="hidden" name="plan[]" value="{{ $plan }}" />
        @endforeach


        @if( !$user->stripe_id )
        <div class="form-row">
          <label for="card-element">

          </label>
          <div id="card-element">
            <!-- a Stripe Element will be inserted here. -->
          </div>

          <!-- Used to display form errors -->
          <div id="card-errors" role="alert"></div>
        </div>

        <button class="btn btn-xs btn-info">Submit Payment</button>
      </form>
      @else

        <form action="{{ route('subscribe') }}" method="POST">
          {{ csrf_field() }}
          <button class="btn btn-xs btn-info">Subscribe</button>
        </form>

      @endif
    </div>

  </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">


var stripe = Stripe('{{ env('STRIPE_KEY') }}');
var elements = stripe.elements();
// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    lineHeight: '18px',
    fontFamily: '"Raleway", Helvetica, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};

// Create an instance of the card Element
var card = elements.create('card', {
  style: style,
  hidePostalCode: true
});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Handle form submission
var form = document.getElementById('payment-form');

form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server
      stripeTokenHandler(result.token);
    }
  });

});

function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);

  // Submit the form
  form.submit();
}
</script>

@endsection
