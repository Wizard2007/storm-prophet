var buttons = document.getElementsByTagName('button');
for(let i = 0; i < buttons.length; i++) {
	let button = buttons[i];
	if(button.className == 'extend') {
		button.addEventListener('click', function(event) {
			event.preventDefault();
			let queue = [];
			for(let j = 0; j < months.length; j++) {
				let month = months[j];
				if(month.getAttribute('href').substr(3, 4) == event.target.parentNode.innerHTML.substr(0, 4) && month.className != 'complete') {
					queue.push(month);
				}
			}
			extend(queue);
		});
	}
	else if(button.className.substr(0, 6) == 'switch') {
		button.addEventListener('click', function(event) {
			event.preventDefault();
			let switches = document.getElementsByClassName('switch');
			let style = document.getElementById('switch-style');
			let sheet = style.sheet ? style.sheet : style.styleSheet;
			let objClass;
			let switchClass;
			if(event.target.innerHTML == "T") {
				objClass = 'text';
			}
			else if(event.target.innerHTML == "\u2205") {
				objClass = 'rect.zero';
			}
			else if(event.target.innerHTML == "\u0394") {
				objClass = 'rect.dynamic';
			}
			else if(event.target.innerHTML == "x\u0305") {
				objClass = 'rect.average';
			}
			else if(event.target.innerHTML == "\u03C3") {
				objClass = 'rect.deviation';
			}
			else if(event.target.innerHTML == "| |") {
				objClass = 'rect.abs';
			}
			if(event.target.className == 'switch on') {
				if(sheet.cssRules) {
					for(let i = 0; i < sheet.cssRules.length; i++) {
						console.log(sheet.cssRules[i].selectorText, 'svg ' + objClass, sheet.cssRules[i].selectorText == 'svg ' + objClass);
						if(sheet.cssRules[i].selectorText == 'svg ' + objClass) {
							sheet.deleteRule(i);
						}
					}  
				}
				switchClass = 'switch';
			}
			else {
				sheet.insertRule('svg ' + objClass + ' { display: block !important; }', sheet.cssRules.length);
				switchClass = 'switch on';
			}
			for(let i = 0; i < switches.length; i++) {
				if(switches[i].innerHTML == event.target.innerHTML) {
					switches[i].className = switchClass;
				}
			}
		});
	}
}