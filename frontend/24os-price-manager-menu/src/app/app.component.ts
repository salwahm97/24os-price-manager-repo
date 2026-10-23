import { Component } from '@angular/core';
import { MenuService } from './shared/api/menu.service';
import { NotificationService, toastrPayload } from './shared/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from './shared/language.service';
import { IndividualConfig } from 'ngx-toastr';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  menu_name !: string;
  permalink !: string;
  responseData : any;

  responseLang: any;
  toast!: toastrPayload;
  screen_loader!:boolean;

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private service:MenuService,
    private cs: NotificationService
    )
  {
    translate.addLangs(['en', 'de']);
    translate.setDefaultLang('en');

  }

  selectLanguage(lang: string)
  {
    this.serviceLang.setLang(lang).subscribe(response => {
      this.responseLang = response;
      this.screen_loader = false;
      if(this.responseLang.message == "language switched successfully"){
        if(this.responseLang.lang == "en"){
          this.toastShow('success', 'Success', 'Language Switched Successfully');
        }
        else
        {
          this.toastShow('success', 'Erfolg', 'Sprache Erfolgreich Umgeschaltet');
        }

      }

    });
  }

  ngOnInit(): void {
    let paramsString = window.location.search;
    let searchParams = new URLSearchParams(paramsString);
    let menu_id = Number(searchParams.get("menu_id"));
    if(Number.isInteger(menu_id) && menu_id > 0){
      this.getMenuById(menu_id);
    }
  }

  getMenuById(menu_id:any){
    this.service.getMenuById(menu_id).subscribe(response => {
      this.responseData = response;
      this.menu_name = this.responseData.menu;
      this.permalink = this.responseData.permalink;
    });
  }

  toastShow(type: string, title: string, message:string){
    this.toast = {
      message: message,
      title: title,
      type: type,
      ic:{
        timeOut: 2500,
        positionClass: "toast-bottom-right",
      } as IndividualConfig,
    };
    this.cs.showToast(this.toast);
  }
}
