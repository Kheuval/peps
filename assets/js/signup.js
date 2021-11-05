"use strict";

function checkPwd() {
    if (form1.pwd.value !== form1.pwd2.value) {
        form1.pwd2.style = 'outline:red solid 3px';
        form1.submit.disabled = true;
    } else {
        form1.pwd2.style = 'outline:none';
        form1.submit.disabled = false;
    }
}
