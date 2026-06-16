const user_id = document.getElementById('user_id').value;
const product_id = document.getElementById('product_id').value;
const product_name = document.getElementById('product_name').value;
const product_price = document.getElementById('product_price').value;
const product_detail = document.getElementById('product_detail').value;
const user_name = document.getElementById('user_name').value;
const user_lastname = document.getElementById('user_lastname').value;
const user_phone = document.getElementById('user_phone').value;
const user_email = document.getElementById('user_email').value;
const key_openpay = document.getElementById('key_openpay').value;
const id_openpay = document.getElementById('id_openpay').value;

var order_id, order, charge_id;

generateOrderId();
sendOpenpayData();

//-----------functions------------
async function generateOrderId() {
  await axios.post('/openpay-order').then((r) => {
    order_id = r.data;
  });
  order = 'promolider2023-' + order_id;
}

async function sendOpenpayData() {
  var myHeaders = new Headers();
  myHeaders.append('Authorization', key_openpay);
  myHeaders.append('Content-Type', 'application/json');
  let fechaFormateada = generateDatetime();

  var raw = JSON.stringify({
    method: 'card',
    amount: product_price,
    currency: 'USD',
    description: product_detail,
    order_id: order,
    confirm: 'false',
    send_email: 'false',
    redirect_url: 'https://crm.promolider.org/' + 'login',
    due_date: fechaFormateada,
    customer: {
      name: user_name,
      last_name: user_lastname,
      phone_number: user_phone,
      email: user_email,
    },
  });

  var requestOptions = {
    method: 'POST',
    headers: myHeaders,
    body: raw,
    redirect: 'follow',
  };

  await fetch(`https://api.openpay.pe/v1/${id_openpay}/charges`, requestOptions)
    .then((r) => r.json())
    .then((r) => {
      charge_id = r.id;
      redirection = r.payment_method.url;
      storePaymentData();
    });
  window.location.href = redirection;
}

function generateDatetime() {
  let fechaActual = new Date();
  let anio = fechaActual.getFullYear();
  let mes = (fechaActual.getMonth() + 1).toString().padStart(2, '0');
  let dia = fechaActual.getDate().toString().padStart(2, '0');
  let hora = fechaActual.getHours().toString().padStart(2, '0');
  let minuto = fechaActual.getMinutes().toString().padStart(2, '0');
  let segundo = fechaActual.getSeconds().toString().padStart(2, '0');
  let fechaFormateada = `${anio}-${mes}-${dia}T${hora}:${minuto}:${segundo}`;
  return fechaFormateada;
}

async function storePaymentData() {
  const formData = new FormData();
  // validar metodo de pago vacio y demas
  formData.append('product_id', product_id);
  formData.append('user_id', user_id);
  formData.append('openpay_order_id', charge_id);
  formData.append('product_name', product_name);
  formData.append('product_detail', product_detail);
  formData.append('product_price', product_price);

  await fetch('/unverified-payment/create', {
    method: 'POST',
    body: JSON.stringify(Object.fromEntries(formData)),
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })
    .then((response) => response.json())
    .catch((error) => console.error(error));
}
