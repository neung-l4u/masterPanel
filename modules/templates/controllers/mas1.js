const setHex = (param,box) => { //for set text in span follow color picker
    const showTextBox = $("#"+box);
    showTextBox.html(param);
    return true;
}//const

const changeTab = (param) => {
    let sel = document.querySelector('#'+param);
    bootstrap.Tab.getOrCreateInstance(sel).show();
}//changeTab