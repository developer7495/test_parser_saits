class Parser{
	constructor(nameApi, isbn){
		this.nameApi       = nameApi;
		this.isbn          = isbn;
		this.SERVER_ERR_ID = 'err_get_page';
		this.$resultDiv    = document.getElementById(`result-${nameApi}`);
	}
	
	getBook(){
		this.$resultDiv.innerHTML = 'Load data ...';
		
		fetch(`?api=${this.nameApi}&isbn=${this.isbn}`)
			.then((response)=>{
				return response.text();
			})
			.then(html => {
				this.renderBook(html);
			})
			.catch(err => {
					console.log('Error search: ', err);
					
					const $err = document.createElement('span');
					
					$err.classList.add('bg-danger','text-white');
					$err.innerHTML = 'Error. See more in the console!';
					
					this.renderToResult($err);
				}
			);
	}
	
	renderBook(html){
		const parserResult = this.parseHtml(html);
		
		this.renderToResult(parserResult);
	};
	
	parseHtml(html){
		const $tmpWrapper = document.createElement('div');
		
		$tmpWrapper.innerHTML = html;
		
		const $err = $tmpWrapper.querySelector(`#${this.SERVER_ERR_ID}`);
		
		if($err){
			return $err;
		}
		
		const $bookWraper = $tmpWrapper.querySelector(this.selectorBookWrap);
		
		if(!$bookWraper){
			const $emptyBook = document.createElement('span');
			$emptyBook.classList.add('bg-warning','text-white');
			$emptyBook.innerText = `Book with ISBN ${this.isbn} not found!`;
			
			return $emptyBook;
		}
		
		const $a = $bookWraper.querySelectorAll('a');
		
		const self = this;
		
		$a.forEach(element => {
			const href = self.prefixUrl + element.getAttribute('href');
			element.setAttribute('href', href)
		});
		
		return $bookWraper;
	}
	
	renderToResult(itemCollectionDom){
		this.$resultDiv.innerHTML='';
		
		if(!itemCollectionDom){ return;	}
		
		this.$resultDiv.appendChild(itemCollectionDom);
	}
}

class ParserAmazon extends Parser{
	constructor(isbn){
		super('amazon', isbn);
		
		this.prefixUrl        = 'https://www.amazon.com';
		this.selectorBookWrap = `[data-asin="${this.isbn}"]`;
	}
}

class ParserBookDepository extends Parser{
	constructor(isbn){
		super('bookdepository', isbn);
		
		this.prefixUrl        = 'https://www.bookdepository.com';
		this.selectorBookWrap = `div.item-block`;
	}
}

window.onload = ()=>{
	document
		.getElementById('btn-search')
		.addEventListener('click', function(){
			const $isbnInput = document.getElementById('input-search');
			const valueInput = $isbnInput.value;
			
			if(!valueInput){
				alert('Input book ISBN!');
				return
			}
			
			document
				.getElementById('result-general')
				.classList.remove('d-none');
			
			new ParserAmazon(valueInput).getBook();
			new ParserBookDepository( valueInput).getBook();
		});
};