import { Component, OnInit, ViewChild } from '@angular/core';
import { allergenModel } from '../model/allergen-model';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatDialog } from '@angular/material/dialog';
import { AllergenService } from '../shared/api/allergen.service';
import { AllergenPopupComponent } from '../allergen-popup/allergen-popup.component';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-allergen',
  templateUrl: './allergen.component.html',
  styleUrls: ['./allergen.component.css']
})
export class AllergenComponent implements OnInit {

  allergenData!: MatTableDataSource<allergenModel>;
  displayedColumns: string[] = ['position', 'allergen', 'allergen_desc', 'actions'];

  @ViewChild(MatPaginator)
  paginator!: MatPaginator;
  @ViewChild(MatSort)
  sort!: MatSort;

  allergenDataById:any;

  toast!: toastrPayload;
  screen_loader!:boolean;

  responseLang: any;
  responseData:any;

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private allergenDialog: MatDialog,
    private service:AllergenService,
    private cs: NotificationService
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {
    this.getAllergens();
    this.service.requiredRefresh.subscribe(r=>{
      this.getAllergens();
    });

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

  getAllergens(){
    this.screen_loader = true;
    this.service.getAllergens().subscribe(response => {
      this.screen_loader = false;
      this.allergenData = new MatTableDataSource(response);
      this.allergenData.paginator = this.paginator;
      this.allergenData.sort = this.sort;
    });
  }

  openAllergenPopupAdd(action:string, id=0): void {
    const dialogRef = this.allergenDialog.open(AllergenPopupComponent, {
      width:'400px',
      panelClass: 'fullscreen-dialog',
      data:{title:"Create New Allergen", action:action, id:0, allergen:'', allergen_desc:''},
    });

    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed ');
    });
  }

  openAllergenPopupEdit(action:string, id:any): void {

    this.service.getAllergenById(id).subscribe(response => {
      this.allergenDataById = response;
      const dialogRef = this.allergenDialog.open(AllergenPopupComponent, {
        width:'400px',
        panelClass: 'fullscreen-dialog',
        data:{title:"Edit Allergen", action:action, id:id, allergen:this.allergenDataById.allergen, allergen_desc:this.allergenDataById.allergen_desc},
      });

    });
  }

  confirmDelete(id:any, allergen:string): void {
    var deleteConfirm= confirm("Are you sure to delete allergen ("+allergen+") ?");
    if(deleteConfirm){
      this.screen_loader = true;
      this.service.deleteAllergen(id).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if(this.responseData.message == 'allergen deleted successfully')
        {
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'Allergen Deleted Successfully');
          }
          else{
            this.toastShow('success', 'Erfolg', 'Allergen Erfolgreich Gelöscht');
          }
        }

      });
    }
  }

  applyFilter(event: Event) {
    const filterValue = (event.target as HTMLInputElement).value;
    this.allergenData.filter = filterValue.trim().toLowerCase();

    if (this.allergenData.paginator) {
      this.allergenData.paginator.firstPage();
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
