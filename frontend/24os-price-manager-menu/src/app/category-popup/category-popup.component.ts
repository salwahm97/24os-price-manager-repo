import { Component, Inject, OnInit } from '@angular/core';
import { ItemsService } from '../shared/api/items.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-category-popup',
  templateUrl: './category-popup.component.html',
  styleUrls: ['./category-popup.component.css']
})
export class CategoryPopupComponent implements OnInit {

  selectedFiles?: FileList;
  selectedFileNames: string[] = [];
  preview !: string;
  preview_name !: string;
  category_image !: boolean;

  responseData:any;
  responseLang: any;

  loader!:boolean;
  screen_loader!:boolean;
  toast!: toastrPayload;

  menu_id !: number;
  index !: number;

  reactiveCategoryForm = new FormGroup({
    category_name: new FormControl('', Validators.required),
    category_desc: new FormControl(),
  });


  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private cs: NotificationService,
    private service:ItemsService,
    private categoryDialogRef:MatDialogRef<CategoryPopupComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {
    this.reactiveCategoryForm.controls.category_name.setValue(this.data.category_name);
    this.reactiveCategoryForm.controls.category_desc.setValue(this.data.category_desc);
    this.menu_id = this.data.menu_id;
    this.index = this.data.index;
    this.category_image = this.data.category_image;
    this.preview = this.data.preview;


    translate.addLangs(['en', 'de']);
    translate.setDefaultLang('en');
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

  selectFileImage(event: any): void {

    this.selectedFileNames = [];
    this.selectedFiles = event.target.files;

    if (this.selectedFiles && this.selectedFiles[0]) {
      const numberOfFiles = this.selectedFiles.length;
      for (let i = 0; i < numberOfFiles; i++) {
        const reader = new FileReader();

        reader.onload = (e: any) => {
          this.preview = e.target.result;
        };

        reader.readAsDataURL(this.selectedFiles[i]);

        this.selectedFileNames.push(this.selectedFiles[i].name);
      }
    }
    this.preview_name = this.selectedFileNames[0];
  }

  submitCategoryDialog(action:string, menu_id:number, id:any, lastindex:number, preview:string){
    if(this.reactiveCategoryForm.valid){
      if(action == 'create' && id == 0 && menu_id > 0)
      {
        this.loader = true;
        this.screen_loader = true;

        this.service.insertCategory(this.reactiveCategoryForm.getRawValue(), menu_id, lastindex + 1, preview).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "category created successfully"){
            if(this.responseData.lang == "en"){

              this.toastShow('success', 'Success', 'Category Created Successfully');
            }
            else
            {
              this.toastShow('success', 'Erfolg', 'Kategorie Erfolgreich Erstellt');
            }
            this.categoryDialogRef.close();
          }
        });
      }
      else if(action == 'edit' && id > 0 && menu_id > 0)
      {
        this.loader = true;
        this.screen_loader = true;

        this.service.updateCategory(this.reactiveCategoryForm.getRawValue(), id, menu_id, preview).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;

          if(this.responseData.message == "category updated successfully"){
            if(this.responseData.lang == "en"){

              this.toastShow('success', 'Success', 'Category Updated Successfully');
            }
            else
            {
              this.toastShow('success', 'Erfolg', 'Kategorie Erfolgreich Aktualisiert');
            }
            this.categoryDialogRef.close();
          }

        });
      }
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
