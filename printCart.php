<?php

?>

<!DOCTYPE html>
<html>
  <head>
   <style>
   
   .invoice-box {
  max-width: 800px;
  margin: auto;
  padding: 30px;
  border: 1px solid #eee;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
  font-size: 16px;
  line-height: 24px;
  font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
  color: #555;
}

.invoice-box table {
  width: 100%;
  line-height: inherit;
  text-align: left;
}

.invoice-box table td {
  padding: 5px;
  vertical-align: top;
}

.invoice-box table tr td:nth-child(n + 2) {
  text-align: right;
}

.invoice-box table tr.top table td {
  padding-bottom: 20px;
}

.invoice-box table tr.top table td.title {
  font-size: 45px;
  line-height: 45px;
  color: #333;
}

.invoice-box table tr.information table td {
  padding-bottom: 40px;
}

.invoice-box table tr.heading td {
  background: #eee;
  border-bottom: 1px solid #ddd;
  font-weight: bold;
}

.invoice-box table tr.details td {
  padding-bottom: 20px;
}

.invoice-box table tr.item td {
  border-bottom: 1px solid #eee;
}

.invoice-box table tr.item.last td {
  border-bottom: none;
}

.invoice-box table tr.item input {
  padding-left: 5px;
}

.invoice-box table tr.item td:first-child input {
  margin-left: -5px;
  width: 100%;
}

.invoice-box table tr.total td:nth-child(2) {
  border-top: 2px solid #eee;
  font-weight: bold;
}

.invoice-box input[type="number"] {
  width: 60px;
}

@media only screen and (max-width: 600px) {
  .invoice-box table tr.top table td {
    width: 100%;
    display: block;
    text-align: center;
  }

  .invoice-box table tr.information table td {
    width: 100%;
    display: block;
    text-align: center;
  }
}

/** RTL **/
.rtl {
  direction: rtl;
  font-family: Tahoma, "Helvetica Neue", "Helvetica", Helvetica, Arial,
    sans-serif;
}

.rtl table {
  text-align: right;
}

.rtl table tr td:nth-child(2) {
  text-align: left;
}

   
   </style>
  </head>
  <body>
<div class="invoice-box">
  <table cellpadding="0" cellspacing="0">
    <tr class="top">
      <td colspan="4">
        <table>
          <tr>
            <td class="title">
				Nuts and Bolts
            </td>

            <td>
              Invoice #: 123<br> Created: March 30, 2021<br> 
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr class="information">
      <td colspan="4">
        <table>
          <tr>
            <td>
              1234 Easy Street<br> Hardwareville FL 9001<br> Phone number: 867-530-9999

            </td>

            <td>
              John Doe<br> john@example.com<br>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr class="heading">
      <td>Item</td>
      <td>Unit Cost</td>
      <td>Quantity</td>
      <td>Price</td>
    </tr>

    <tr class="item" v-for="item in items">
      <td>Screwdriver RR-123-RR</td>
      <td>$33</td>
      <td>2</td>
      <td>$66</td>
    </tr>
	<tr class="item" v-for="item in items">
      <td>Nails	SS-111-SS</td>
      <td>$2.22</td>
      <td>9</td>
      <td>$19.98</td>
    </tr>
	<tr class="item" v-for="item in items">
      <td>Hammer rr-98-th</td>
      <td>$12.22</td>
      <td>1</td>
      <td>$12.22</td>
    </tr>

    <tr class="total">
      <td colspan="3"></td>
      <td>Total: $98.2</td>
    </tr>
  </table>
</div>
  </body>
</html>
