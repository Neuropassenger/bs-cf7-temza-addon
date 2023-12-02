function getURLParameter(sParam) {
    const sPageURL = window.location.search.substring(1);
    const sURLVariables = sPageURL.split('&');

    for (let i = 0; i < sURLVariables.length; i++) {
        const sParameter = sURLVariables[i].split('=');
        if (sParameter[0] === sParam) {
            return sParameter[1];
        }
    }
    return false;
}

function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return false;
}

if (getURLParameter('utm_source') !== false) {
    setCookie(
        'bs_utm_data',
        getURLParameter('utm_source') + '|' + getURLParameter('utm_medium') + '|' + getURLParameter('utm_term') + '|' + getURLParameter('utm_content') + '|' + getURLParameter('utm_campaign'),
        60
    );

    if (getCookie('bs_landing_page') === false)
        setCookie(
            'bs_landing_page',
            window.location.href.split('?')[0],
            60
        );

    if(document.referrer != '' && document.referrer.indexOf(location.protocol + "//" + location.host) !== 0 && getCookie('bs_referer') === false) {
       setCookie(
            'bs_referer',
            document.referrer,
            60
       );
    }
}