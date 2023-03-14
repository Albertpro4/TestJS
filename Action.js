var form = document.querySelector('#form');

form.addEventListener('submit', function(e) {
e.preventDefault();

var name = document.querySelector('#name').value;
var email = document.querySelector('#email').value;
var phone = document.querySelector('#phone').value;
var price = document.querySelector('#price').value;

var crm = new AmoCRM('client_id', 'client_secret', 'redirect_uri');

crm.auth('code').then(function() {
return crm.createContact(name, email, phone);
}).then(function(contact_id) {
return crm.createDeal(contact_id, price);
}).then(function(deal_id) {
console.log('Сделка успешно создана');
}).catch(function(error) {
console.error('Ошибка: ' + error.message);
});
});