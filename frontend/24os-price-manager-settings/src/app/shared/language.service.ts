import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Subject, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LanguageService {
  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/lang/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/lang/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }

  constructor(private httpClient: HttpClient) { }

  getLang(){
    return this.httpClient.get(this.url);
  }

  setLang(lang:string){
    return this.httpClient.post(this.url, {lang:lang}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

}
