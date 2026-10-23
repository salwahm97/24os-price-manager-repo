import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AllergenService {
  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/allergen/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/allergen/';

  constructor(private httpClient: HttpClient) { }

  getAllergens():Observable<any[]>{
    return this.httpClient.get<any[]>(this.url);
  }
}
