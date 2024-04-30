Objective Test
POST
/transacao
NEED AUTH for consult
{
"conta_id": 123456,
"valor": 12,
"forma_pagamento" : "D"
}
POST
/conta
{
"conta_id": 123456,
"password": "1324",
"valor": 500
}
GET
/conta?id=<ID>
POST
/login
{
"conta_id": 12345,
"password": "1324"
}
