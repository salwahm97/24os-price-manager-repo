import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from './shared/language.service';
import { NotificationService, toastrPayload } from './shared/notification.service';
import { BreakpointObserver, BreakpointState, Breakpoints } from '@angular/cdk/layout';
import { IndividualConfig } from 'ngx-toastr';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  toast!: toastrPayload;
  screen_loader!:boolean;

  isPhoneviewed = false;
  responseLang: any;

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private cs: NotificationService,
    public responsive: BreakpointObserver
  ) {

    translate.addLangs(['en', 'de']);
    translate.setDefaultLang('en');

  }

  ngOnInit() {
    this.responsive.observe([Breakpoints.HandsetPortrait]).subscribe((state: BreakpointState) => {
      this.isPhoneviewed = false;
      if (state.matches) {
        this.isPhoneviewed = true;
        console.log('This is the Handset Portrait point at max-width: 599.98 px and portrait orientation.');
      }
    });
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
