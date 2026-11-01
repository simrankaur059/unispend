(() => {
    const dataElement = document.getElementById('category-chart-data');
    const canvas = document.getElementById('category-chart');
    const legend = document.getElementById('chart-legend');

    if (!dataElement || !canvas || !legend) {
        return;
    }

    const categories = JSON.parse(dataElement.textContent);
    const colors = ['#6c63ff', '#ff9f43', '#23b58b', '#ef6b78', '#4b9ee8', '#be78d1', '#91a0b7'];
    const context = canvas.getContext('2d');
    const total = categories.reduce((sum, item) => sum + Number(item.total), 0);

    if (!context || total <= 0) {
        return;
    }

    let startAngle = -Math.PI / 2;
    categories.forEach((item, index) => {
        const slice = (Number(item.total) / total) * Math.PI * 2;
        context.beginPath();
        context.moveTo(canvas.width / 2, canvas.height / 2);
        context.arc(canvas.width / 2, canvas.height / 2, 112, startAngle, startAngle + slice);
        context.closePath();
        context.fillStyle = colors[index % colors.length];
        context.fill();
        startAngle += slice;

        const listItem = document.createElement('li');
        const dot = document.createElement('span');
        const label = document.createElement('span');
        const amount = document.createElement('strong');

        dot.className = 'legend-dot';
        dot.style.backgroundColor = colors[index % colors.length];
        label.textContent = item.name;
        amount.textContent = `Rs. ${Number(item.total).toFixed(2)}`;
        listItem.append(dot, label, amount);
        legend.appendChild(listItem);
    });
})();
