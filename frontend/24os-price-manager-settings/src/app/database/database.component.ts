import { Component, OnInit } from '@angular/core';
import { DatabaseService } from '../shared/api/database.service';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-database',
  templateUrl: './database.component.html',
  styleUrls: ['./database.component.css']
})
export class DatabaseComponent implements OnInit {

  loader!:boolean;
  screen_loader!:boolean;
  toast!: toastrPayload;

  responseData:any;
  responseLang :any;

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private cs: NotificationService,
    private service:DatabaseService
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {
    this.serviceLang.getLang().subscribe(response => {
      this.responseLang = response;
      this.translate.use(this.responseLang);
    });

    this.serviceLang.requiredRefresh.subscribe(r=>{
      this.serviceLang.getLang().subscribe(response => {
        this.responseLang = response;
        this.translate.use(this.responseLang);
      });
    });
  }

  resetDatabase(){
    var resetDatabaseConfirm= confirm("Are you sure to Reset Database ?");
    if(resetDatabaseConfirm){
      this.loader = true;
      this.screen_loader = true;
      this.service.resetDatabase().subscribe(response => {
        this.responseData = response;
        this.screen_loader = false;
        this.loader = false;
        if(this.responseData.message == "database reset successfully"){
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Database Reset Successfully');
          }
          else{
            this.toastShow('success', 'Erfolg', 'Datenbank Erfolgreich Zurückgesetzt');
          }

        }

      });
    }
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
