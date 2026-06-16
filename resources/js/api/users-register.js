const username = document.getElementById('username');
const email = document.getElementById('email');
const password = document.getElementById('password');
const repassword = document.getElementById('repassword');
const user_type = document.getElementById('user_type');
const firstName = document.getElementById('name');
const lastName = document.getElementById('last_name');
const biography = document.getElementById('biography');
const phone = document.getElementById('phone');
const date_birth = document.getElementById('date_birth');
const nro_document = document.getElementById('nro_document');
const country = document.getElementById('country');
const operation_number = document.getElementById('operation_number');
const accountTypes = document.getElementById('id_account_type');
const accountTypesPrice = document.getElementById('account_type-price');
const accountTypesIva = document.getElementById('account_type-iva');
const accountTypesTotalCostMembreship = document.getElementById('account_type-total_cost_membreship');
const documentTypes = document.getElementById('id_document_type');
const payment_method = document.getElementById('payment_method_id');
const purchase_number = document.getElementById('purchase_number');
const account_types = document.getElementById('account_types');
const key_openpay = document.getElementById('key_openpay');
const id_openpay = document.getElementById('id_openpay');
let stepper = new Stepper(document.querySelector('.bs-stepper'));
var optionRegisterSelected = 0; // 0 -> registro pagado, 1 -> registro gratis
var charge_id = null;
var total_amount = null;
document.getElementById('btnFree').style.display = "none";
document.getElementById('input_operation').style.display = "none";
document.getElementById('info_operation').style.display = "none";
document.getElementById('info_operation2').style.display = "none";
document.getElementById('btnUnverified').style.display = "none";
document.getElementById('checker').style.display = "none";
document.getElementById('alert').style.display = "none";

function accountTypesChanged(list_account_type) {
    const option_actual = accountTypes.value

    list_account_type.forEach(element => {

        if (element.id == option_actual) {
            if(element.account == 'Basic'){
                optionRegisterSelected = 1;
                document.getElementById('btnPay').style.display = "none";
                document.getElementById('btnFree').style.display = "block"
                document.getElementById('payment_method_selector').style.visibility = "hidden";
            }else{
                document.getElementById('btnFree').style.display = "none";
                document.getElementById('btnPay').style.display = "block";
                document.getElementById('payment_method_selector').style.visibility = "visible";
            }

            if (option_actual === '5') {
                payment_method.value = "0"
            }
            accountTypesPrice.value = '$ ' + Number.parseFloat(element.price).toFixed(2);
            accountTypesIva.value = element.iva + '%';
            let amount = Number.parseFloat(element.total).toFixed(2);
            accountTypesTotalCostMembreship.value = '$ ' + amount;
            total_amount = amount;
        }
    });
};

//If Perú is selected, show transfer method pay
function countrySelected(payment_methods) {
    limpiarSelect(payment_method);
    payment_methods.forEach(element => {
        var option = document.createElement("option");
        option.innerHTML = element.name;
        option.value = element.id;
        payment_method.appendChild(option);
    });
}

function paymentMethodSelected(){
    if(payment_method.value == "3"){
        document.getElementById('input_operation').style.display = "block";
        document.getElementById('info_operation').style.display = "block";
        document.getElementById('info_operation2').style.display = "block";
        document.getElementById('btnFree').style.display = "none";
        document.getElementById('btnPay').style.display = "none";
        document.getElementById('btnUnverified').style.display = "block";
        document.getElementById('info_openpay').style.display = "none";
    }
    if(payment_method.value == "1"){
        document.getElementById('input_operation').style.display = "none";
        document.getElementById('info_operation').style.display = "none";
        document.getElementById('info_operation2').style.display = "none";
        document.getElementById('btnFree').style.display = "none";
        document.getElementById('btnPay').style.display = "block";
        document.getElementById('btnUnverified').style.display = "none";
        document.getElementById('info_openpay').style.display = "none";
    }
    // else{
    //     document.getElementById('input_operation').style.display = "none";
    //     document.getElementById('info_operation').style.display = "none";
    //     document.getElementById('info_operation2').style.display = "none";
    //     document.getElementById('btnFree').style.display = "none";
    //     document.getElementById('btnPay').style.display = "block";
    //     document.getElementById('btnUnverified').style.display = "none";
    //     document.getElementById('info_openpay').style.display = "none";
    // }
}

//show account type after select user type
function userTypesChanged(list_account_type) {
    limpiarSelect(accountTypes);
    const option_actual = user_type.value
    indice_basic = list_account_type.findIndex(array => array.account == 'Basic')
    indice_guest = list_account_type.findIndex(array => array.account == 'Guest')
    if (option_actual == 'Producer') {
        list_account_type.splice(indice_guest)
    }
    else if (option_actual == 'Distributor') {
        list_account_type.splice(indice_basic, 1)
    }
    list_account_type.forEach(element => {
        var option = document.createElement("option");
        option.innerHTML = element.account
        option.value = element.id
        accountTypes.appendChild(option)
    });

    accountTypesChanged(list_account_type)
};

function limpiarSelect(object) {
    for (let i = object.options.length; i >= 0; i--) {
        object.options[i] = null;
    }
}

/////////////////////////// Function Validation
function validDocumentType(list_document_type) {
    // Limpiar el valor del campo nro_document cuando se selecciona un nuevo tipo de documento
    nro_document.value = "";

    // Restablecer el estilo del campo al estado normal (sin errores)
    nro_document.classList.remove('is-invalid', 'is-valid');

    // Obtener el tipo de documento seleccionado
    const option_actual = documentTypes.options[documentTypes.selectedIndex].text;

    // Ajustar el maxlength y minlength según el tipo de documento
    list_document_type.forEach(element => {
        if (option_actual == "DNI") {
            nro_document.setAttribute("maxlength", "18");
            nro_document.setAttribute("minlength", "1");
        } else {
            nro_document.setAttribute("maxlength", "18");
            nro_document.setAttribute("minlength", "1");
        }
    });
}

function validDocumentTypeStyles(id) {
    let element = document.getElementById(id);
    const option_actual = documentTypes.options[documentTypes.selectedIndex].text;
    const tamaño = element.value;

    // Solo validar si hay algo escrito
    if (tamaño.length > 0) {
        if (option_actual == "DNI" && tamaño.length < 8) {
            element.classList.add('is-invalid');
            element.classList.remove('is-valid');
        } else if (option_actual != "DNI" && tamaño.length < 12) {
            element.classList.add('is-invalid');
            element.classList.remove('is-valid');
        } else {
            element.classList.add('is-valid');
            element.classList.remove('is-invalid');
        }
    } else {
        // Si no hay valor, eliminar cualquier clase de validación
        element.classList.remove('is-invalid', 'is-valid');
    }
}

function numberOnly(id) {
    let element = document.getElementById(id);
    element.value = element.value.replace(/[^0-9]/g, "");

    // Validar solo después de que el usuario haya escrito algo
    if (element.value.length > 0) {
        validDocumentTypeStyles(id);
    }
}


function lettersOnly(e) {
    tecla = (document.all) ? e.keyCode : e.which;

    if (tecla == 8 || tecla == 32) {
        return true;
    }

    patron = /[A-Za-z]/;
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}

function checkCharactersSpecial(e) {
    tecla = (document.all) ? e.keyCode : e.which;

    if (tecla == 8) {
        return true;
    }

    patron = /[A-Za-z0-9]/;
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}

function validDateBirth() {
    const date_now = new Date();
    let year_now = date_now.getFullYear();
    let minLegalAge = year_now - 18;
    date_birth.setAttribute('max', "" + minLegalAge + "-01-01")
}



//////////////////////////////////////////
function printError(listaErrores) {
    clearError();
    if (listaErrores.username) {
        username.setAttribute('class', 'form-control is-invalid');
        document.getElementById('username_error').innerHTML = listaErrores.username;
    }
    if (listaErrores.email) {
        email.setAttribute('class', 'form-control is-invalid');
        document.getElementById('email_error').innerHTML = listaErrores.email;
    }
    if (listaErrores.password) {
        password.setAttribute('class', 'form-control is-invalid');
        document.getElementById('password_error').innerHTML = listaErrores.password;
    }
    if (listaErrores.user_type) {
        user_type.setAttribute('class', 'form-control is-invalid');
        document.getElementById('user_type_error').innerHTML = listaErrores.user_type;
    }
    if (listaErrores.id_account_type) {
        accountTypes.setAttribute('class', 'form-control is-invalid');
        document.getElementById('id_account_type_error').innerHTML = listaErrores.id_account_type;
    }
    if (listaErrores.id_document_type) {
        documentTypes.setAttribute('class', 'form-control is-invalid');
        document.getElementById('id_document_type_error').innerHTML = listaErrores.id_document_type;
    }
    if (listaErrores.name) {
        firstName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('name_error').innerHTML = listaErrores.name;
    }
    if (listaErrores.last_name) {
        lastName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('last_name_error').innerHTML = listaErrores.last_name;
    }
    if (listaErrores.biography) {
        biography.setAttribute('class', 'form-control is-invalid');
        document.getElementById('biography_error').innerHTML = listaErrores.biography;
    }
    if (listaErrores.phone) {
        phone.setAttribute('class', 'form-control is-invalid');
        document.getElementById('phone_error').innerHTML = listaErrores.phone;
    }
    if (listaErrores.date_birth) {
        date_birth.setAttribute('class', 'form-control is-invalid');
        document.getElementById('date_birth_error').innerHTML = listaErrores.date_birth;
    }
    if (listaErrores.nro_document) {
        nro_document.setAttribute('class', 'form-control is-invalid');
        document.getElementById('nro_document_error').innerHTML = listaErrores.nro_document;
    }

    if (listaErrores.payment_method) {
        payment_method.setAttribute('class', 'form-control is-invalid');
        document.getElementById('payment_method_id_error').innerHTML = listaErrores.payment_method;
    }

    if (listaErrores.operation_number) {
        operation_number.setAttribute('class', 'form-control is-invalid');
        document.getElementById('operation_number_error').innerHTML = listaErrores.operation_number;
    }
}

function clearError() {
    username.setAttribute('class', 'form-control');
    document.getElementById('username_error').innerHTML = "";
    email.setAttribute('class', 'form-control');
    document.getElementById('email_error').innerHTML = "";
    password.setAttribute('class', 'form-control');
    document.getElementById('password_error').innerHTML = "";
    user_type.setAttribute('class', 'form-control');
    document.getElementById('user_type_error').innerHTML = "";
    accountTypes.setAttribute('class', 'form-control');
    document.getElementById('id_account_type_error').innerHTML = "";
    documentTypes.setAttribute('class', 'form-control');
    document.getElementById('id_document_type_error').innerHTML = "";
    firstName.setAttribute('class', 'form-control');
    document.getElementById('name_error').innerHTML = "";
    lastName.setAttribute('class', 'form-control');
    document.getElementById('last_name_error').innerHTML = "";
    biography.setAttribute('class', 'form-control');
    document.getElementById('biography_error').innerHTML = "";
    phone.setAttribute('class', 'form-control');
    document.getElementById('phone_error').innerHTML = "";
    date_birth.setAttribute('class', 'form-control');
    document.getElementById('date_birth_error').innerHTML = "";
    nro_document.setAttribute('class', 'form-control');
    document.getElementById('nro_document_error').innerHTML = "";
    operation_number.setAttribute('class', 'form-control');
    document.getElementById('operation_number_error').innerHTML = "";
}

function viewError(listaErrores) {
    if (listaErrores.username) {
        stepper.to(1)
    }
    else if (listaErrores.email) {
        stepper.to(1)
    }
    else if (listaErrores.password) {
        stepper.to(1)
    }
    else if (listaErrores.user_type) {
        stepper.to(1)
    }
    else if (listaErrores.id_document_type) {
        stepper.to(2)
    }
    else if (listaErrores.name) {
        stepper.to(2)
    }
    else if (listaErrores.last_name) {
        stepper.to(2)
    }
    else if (listaErrores.biography) {
        stepper.to(2)
    }
    else if (listaErrores.phone) {
        stepper.to(2)
    }
    else if (listaErrores.date_birth) {
        stepper.to(2)
    }
    else if (listaErrores.nro_document) {
        stepper.to(2)
    }
    else if (listaErrores.id_account_type) {
        stepper.to(3)
    }
    else if (listaErrores.operation_number) {
        stepper.to(3)
    }
}

function useRegex(input) {
    let regex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])((?=.*\W)|(?=.*_))^[^ ]+$/g;
    return regex.test(input);
}

function validPassword(new_pas, conf_pas) {
    if (new_pas == '' || new_pas.length <= 0) {
      alert('Ingrese una contraseña');
      return false;
    }

    if (new_pas != conf_pas) {
      alert('Las contraseñas no coinciden');
      return false;
    }
    if (new_pas.length < 8) {
      alert('La contraseña debe tener como mínimo 8 caracteres');
      return false;
    }
    if (!this.useRegex(new_pas)) {
        password.setAttribute('class', 'form-control is-invalid');
        document.getElementById('password_error').innerHTML = "La contraseña debe tener como mínimo 1 letra minúscula,una letra mayúscula, 1 digito y 1 caracter especial";
      return false;
    }
    return true;
  }

var order_id;
var payment;
var openpayOrder;
var redirection;

async function register(url, sponsor) {
    document.getElementById('btnPay').style.display = "none";
    let date_birth = document.getElementById('date_birth');

    let getBirth = new Date(date_birth.value);
    let dateB = getBirth.getFullYear();

    let year_now = new Date().getFullYear();
    let getAge  = year_now - dateB;

    if(getAge < 18){
        return alert('No cumple con la edad requerida');
    }

    let payment = '';
    let charge_id = '';
    let redirection = '';

    if(optionRegisterSelected == 1){
        payment = 'free';
    }else{
        if(payment_method.value == 0){
            payment_method.setAttribute('class', 'form-control is-invalid');
            document.getElementById('payment_method_id_error').innerHTML = "El nombre de usuario debe tener menos de 50 caracteres";
        }else{
            username.setAttribute('class', 'form-control');
            document.getElementById('username_error').innerHTML = "";
        }

        if(payment_method.value == 1){
            payment = 'openpay';
        }

        if(payment_method.value == 2){
            return alert('metodo de pago aun no implementado');
        }
        if(payment_method.value == 3){
            payment = 'binance';
            if(operation_number.value.length <= 4){
                return alert('Ingrese un número de operación válido');
            }
            // Aseguramos que use la URL correcta para transferencia
            url = window.location.origin + '/users/create-unverified-user';
        }

        if(payment_method.value == 4){
            payment = 'paypal';
        }
    }

    if(payment_method.value == 1){
        await axios.post('/openpay-order').then((r) => {
            order_id = r.data
        });
        let account_type_list = JSON.parse(account_types.value);
        let account_selected = account_type_list.filter(obj => obj.id == accountTypes.value)[0];
        let order = "promolider2024-"+order_id;
        let description = "Membresía "+account_selected.account;
        var myHeaders = new Headers();

        myHeaders.append("Authorization", key_openpay.value);
        myHeaders.append("Content-Type", "application/json");

        let fechaActual = new Date();
        let anio = fechaActual.getFullYear();
        let mes = (fechaActual.getMonth() + 1).toString().padStart(2, '0');
        let dia = fechaActual.getDate().toString().padStart(2, '0');
        let hora = fechaActual.getHours().toString().padStart(2, '0');
        let minuto = fechaActual.getMinutes().toString().padStart(2, '0');
        let segundo = fechaActual.getSeconds().toString().padStart(2, '0');
        let fechaFormateada = `${anio}-${mes}-${dia}T${hora}:${minuto}:${segundo}`;

        var raw = JSON.stringify({
            "method": "card",
            "amount": total_amount,
            "currency": "USD",
            "description": description,
            "order_id": order,
            "confirm": "false",
            "send_email": "false",
            "redirect_url": process.env.MIX_APP_URL+"login",
            "due_date": fechaFormateada,
            "customer": {
                "name": firstName.value,
                "last_name": lastName.value,
                "phone_number": phone.value,
                "email": email.value
            }
        });
        var requestOptions = {
            method: 'POST',
            headers: myHeaders,
            body: raw,
            redirect: 'follow'
        };

        await fetch(`https://api.openpay.pe/v1/${id_openpay.value}/charges`, requestOptions).then(r => r.json())
          .then(r => {
              charge_id = r.id;
              redirection = r.payment_method.url;
          });
    }

    const formData = new FormData();
    formData.append('id_referrer_sponsor', sponsor);
    formData.append('username', username.value);
    formData.append('email', email.value);
    formData.append('password', password.value);
    formData.append('password_confirmation', repassword.value);
    formData.append('user_type', user_type.value);
    formData.append('name', firstName.value);
    formData.append('last_name', lastName.value);
    formData.append('biography', biography.value);
    formData.append('phone', phone.value);
    formData.append('date_birth', date_birth.value);
    formData.append('id_document_type', documentTypes.value);
    formData.append('nro_document', nro_document.value);
    formData.append('id_country', country.value);
    formData.append('id_account_type', accountTypes.value);
    formData.append('purchase_number', purchase_number.value);
    formData.append('payment_method_id', payment_method.value);
    formData.append('payment_method', payment);
    formData.append('order_id', charge_id);

    if(payment_method.value == "3"){
        formData.append('operation_number', operation_number.value);
    }

    const btnUnverified = document.getElementById('btnUnverified');
    btnUnverified.disabled = true;

    // Agregamos el token CSRF y headers necesarios
    await fetch(url, {
        method: 'POST',
        body: JSON.stringify(Object.fromEntries(formData)),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
    })
      .then(response => response.json())
      .then(data => {
          const errorContainer = document.getElementById('error-container');
          errorContainer.innerHTML = '';

          if (data.errors) {
              errorContainer.classList.add('alert', 'alert-danger');
              Object.values(data.errors).forEach(errorMessages => {
                  errorMessages.forEach(errorMessage => {
                      const errorDiv = document.createElement('div');
                      errorDiv.classList.add('mb-1');
                      errorDiv.textContent = errorMessage;
                      errorContainer.appendChild(errorDiv);
                  });
              });
          } else {
              if(payment_method.value == "3"){
                  alert('Registro exitoso. Un administrador verificará su pago.');
                  window.location.href = window.location.origin + '/login?status=pending';
              } else if(payment_method.value == "1") {
                  window.location.href = redirection;
              } else {
                  window.location.href = window.location.origin + '/login';
              }
          }

          btnUnverified.disabled = false;

      }).catch(error => {
          console.error('Error en el registro:', error);
          alert('Ocurrió un error durante el registro. Por favor, intente nuevamente.');
          btnUnverified.disabled = false;
      });
}

function stepperValidations1(){
    let value = true;
    let clearUsernameError = true;
    if(username.value == ""){
        username.setAttribute('class', 'form-control is-invalid');
        document.getElementById('username_error').innerHTML = "El nombre de usuario debe tener más de 3 caracteres";
        value = false;
        clearUsernameError = false;
    }
    if(username.value.length >= 50){
        username.setAttribute('class', 'form-control is-invalid');
        document.getElementById('username_error').innerHTML = "El nombre de usuario debe tener menos de 50 caracteres";
        value = false;
        clearUsernameError = false;
    }
    if(clearUsernameError){
        username.setAttribute('class', 'form-control');
        document.getElementById('username_error').innerHTML = "";
    }

    let clearEmailError = true;
    // if(email.value == ""){
    //     email.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('email_error').innerHTML = "Ingrese su correo";
    //     value = false;
    //     clearEmailError = false;
    // }
    let emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
    if(!emailRegex.test(email.value)){
        email.setAttribute('class', 'form-control is-invalid');
        document.getElementById('email_error').innerHTML = "Ingrese un correo válido";
        value = false;
        clearEmailError = false;
    }
    if(clearEmailError){
        email.setAttribute('class', 'form-control');
        document.getElementById('email_error').innerHTML = "";
    }

    let clearPasswordError = true;
    let regex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])((?=.*\W)|(?=.*_))^[^ ]+$/g;
    if(password.value <= 5){
        password.setAttribute('class', 'form-control is-invalid');
        document.getElementById('password_error').innerHTML = "La contraseña debe tener más de 5 caracteres";
        value = false;
        clearPasswordError = false;
    }
    if(!regex.test(password.value)){
        password.setAttribute('class', 'form-control is-invalid');
        document.getElementById('password_error').innerHTML = "La contraseña debe tener como mínimo 1 letra minúscula,una letra mayúscula, 1 digito y 1 caracter especial";
        value = false;
        clearPasswordError = false;
    }
    if(password.value != repassword.value){
        password.setAttribute('class', 'form-control is-invalid');
        document.getElementById('password_error').innerHTML = "Las contraseñas no coinciden";
        value = false;
        clearPasswordError = false;
    }
    if(clearPasswordError){
        password.setAttribute('class', 'form-control');
        document.getElementById('password_error').innerHTML = "";
    }

    let userTypeClearError = true;
    if(user_type.value == "Seleccione una opción"){
        user_type.setAttribute('class', 'form-control is-invalid');
        document.getElementById('user_type_error').innerHTML = "Seleccione un tipo de usuario";
        value = false;
        userTypeClearError = false;
    }
    if(userTypeClearError){
        user_type.setAttribute('class', 'form-control');
        document.getElementById('user_type_error').innerHTML = "";
    }

    return value;
}

function stepperValidations2(){
    let value = true;
    let clearFirstNameError = true;
    if(firstName.value.length <= 3){
        firstName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('name_error').innerHTML = "Ingrese su nombre correctamente";
        value = false;
        clearFirstNameError = false;
    }
    if(firstName.value.length >= 50){
        firstName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('name_error').innerHTML = "Solo se aceptan hasta 50 caracteres";
        value = false;
        clearFirstNameError = false;
    }
    if(clearFirstNameError){
        firstName.setAttribute('class', 'form-control');
        document.getElementById('name_error').innerHTML = "";
    }

    let clearLastNameError = true;
    if(lastName.value.length <= 3){
        lastName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('last_name_error').innerHTML = "Ingrese su nombre correctamente";
        value = false;
        clearLastNameError = false;
    }
    if(lastName.value.length >= 50){
        lastName.setAttribute('class', 'form-control is-invalid');
        document.getElementById('last_name_error').innerHTML = "Solo se aceptan hasta 50 caracteres";
        value = false;
        clearLastNameError = false;
    }
    if(clearLastNameError){
        lastName.setAttribute('class', 'form-control');
        document.getElementById('last_name_error').innerHTML = "";
    }

    let clearPhoneError = true;
    if(phone.value.length <= 7){
        phone.setAttribute('class', 'form-control is-invalid');
        document.getElementById('phone_error').innerHTML = "Ingrese su teléfono correctamente";
        value = false;
        clearPhoneError = false;
    }
    if(clearPhoneError){
        phone.setAttribute('class', 'form-control');
        document.getElementById('phone_error').innerHTML = "";
    }

    let clearDateBirthError = true;
    if(date_birth.value == ""){
        date_birth.setAttribute('class', 'form-control is-invalid');
        document.getElementById('date_birth_error').innerHTML = "Ingrese su fecha de nacimiento";
        value = false;
        clearDateBirthError = false;
    }
    if(clearDateBirthError){
        date_birth.setAttribute('class', 'form-control');
        document.getElementById('date_birth_error').innerHTML = "";
    }

    let clearDocumentTypeError = true;
    if(documentTypes.value == 0){
        documentTypes.setAttribute('class', 'form-control is-invalid');
        document.getElementById('id_document_type_error').innerHTML = "Seleccione su tipo de documento";
        value = false;
        clearDocumentTypeError = false;
    }
    if(clearDocumentTypeError){
        documentTypes.setAttribute('class', 'form-control');
        document.getElementById('id_document_type_error').innerHTML = "";
    }

    let clearNroDocumentError = true;

    // if(documentTypes.value != 3 && nro_document.value.charAt(0) == 0){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "El documento no puede empezar con 0";
    //     value = false;
    //     clearNroDocumentError = false;
    // }

    // if(documentTypes.value == 1 && nro_document.value.length != 18){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "El DNI es de 8 dígitos";
    //     value = false;
    //     clearNroDocumentError = false;
    // }
    // if(documentTypes.value == 2 && nro_document.value.length != 18){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "El pasaporte es de 12 dígitos";
    //     value = false;
    //     clearNroDocumentError = false;
    // }


    // if(documentTypes.value == 3 && nro_document.value.charAt(0) != 0){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "El carnet de extanjería debe empezar con 0";
    //     value = false;
    //     clearNroDocumentError = false;
    // }
    // if(documentTypes.value == 3 && nro_document.value.length != 18){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "El carnet de extanjería es de 18 dígitos";
    //     value = false;
    //     clearNroDocumentError = false;
    // }

    // if(documentTypes.value == 4 && nro_document.value.length > 18){
    //     nro_document.setAttribute('class', 'form-control is-invalid');
    //     document.getElementById('nro_document_error').innerHTML = "Otros documentos tienen como máximo 18 dígitos";
    //     value = false;
    //     clearNroDocumentError = false;
    // }
    if(clearNroDocumentError){
        nro_document.setAttribute('class', 'form-control');
        document.getElementById('nro_document_error').innerHTML = "";
    }

    return value;
}

function next1() {
    if(stepperValidations1()){
        clearError();
        stepper.next()
    }
}

function alerta(){
    let timerInterval;
        Swal.fire({
        title: "Procesando",
        html: "Su solicitud está siendo procesada <b></b>.",
        timer: 2000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            const timer = Swal.getPopup().querySelector("b");
            timerInterval = setInterval(() => {
            timer.textContent = `${Swal.getTimerLeft()}`;
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
        }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            window.location.href = redirection;
            console.log("Close");
        }
    });
}

function next2(){
    if(stepperValidations2()){
        clearError();
        stepper.next()
    }
}

function previus() {
    stepper.previous()
}