function showSidebar() {
    const sidebar = document.querySelector('.sidebar')
    sidebar.style.display = 'flex'
}

function hideSidebar() {
    const sidebar = document.querySelector('.sidebar')
    sidebar.style.display = 'none'
}

let next = document.querySelector('.home-next-btn')
let prev = document.querySelector('.home-prev-btn')

next.addEventListener('click', function () {
    let items = document.querySelectorAll('.item')
    document.querySelector('.home-slide').appendChild(items[0])
})

prev.addEventListener('click', function () {
    let items = document.querySelectorAll('.item')
    document.querySelector('.home-slide').prepend(items[items.length - 1]) // here the length of items = 6
})
