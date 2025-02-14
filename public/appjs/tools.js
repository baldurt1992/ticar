
window.urldomine = document.location.origin + '/';

function rangoutil(totalpage, currentpage) {
    let star, end, total;
    total = (totalpage !== null) ? parseInt(totalpage) : 0;
    if (total <= 5) {
        star = 1;
        end = total + 1
    } else {
        if (currentpage <= 2) {
            star = 1;
            end = 6;
        } else if (currentpage + 2 >= total) {
            star = total - 5;
            end = total + 1
        } else {
            star = currentpage - 2;
            end = currentpage + 3
        }
    }
    return _.range(star, end)
}

toast_options = {
    position: 'bottom-center',
    duration: '3000'
};

function validd (e) {

    let key = e.key;

    const permitidos = ['.', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'ArrowLeft', 'ArrowLeft', 'Delete', 'Tab'];

    if (!permitidos.includes(key)) e.preventDefault()

}

function fixdate (dt) {

   let m =  dt.getMonth() < 9 ? '0' +  (dt.getMonth() + 1) : dt.getMonth() + 1;

   let d = dt.getDate() < 10 ? '0' +  dt.getDate() : dt.getDate();

   return dt.getFullYear() + '-' + m  + '-' + d;

}

