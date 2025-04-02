function getIP(json) {
    // console.log(json.ip);
    const myIP = $("#myIP");
    const agent = $("#agent");
    const readUserAgent = navigator.userAgent;

    myIP.val(json.ip);
    agent.val(readUserAgent);
}