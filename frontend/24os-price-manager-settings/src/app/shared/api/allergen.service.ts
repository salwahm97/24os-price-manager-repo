import { Injectable } from '@angular/core';
import { Observable, Subject, tap } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { allergenModel } from 'src/app/model/allergen-model';

@Injectable({
  providedIn: 'root'
})
export class AllergenService {
  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/allergen/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/allergen/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }
  constructor(private httpClient: HttpClient) { }

  getAllergens():Observable<allergenModel[]>{
    return this.httpClient.get<allergenModel[]>(this.url);
  }

  getAllergenById(id:any){
    return this.httpClient.get<allergenModel[]>(this.url + id);
  }

  insertAllergen(inputData:any){
    return this.httpClient.post(this.url, inputData).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  updateAllergen(inputData:any, id: any){
    return this.httpClient.patch(this.url + id, inputData).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  deleteAllergen(id: any){
    return this.httpClient.delete(this.url + id).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }
}
