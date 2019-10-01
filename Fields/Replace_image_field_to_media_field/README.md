# CASE
Replace Image (File) field to the mMdia field for paragraph or something else.

## Steps:
1. Go to edit an entity type
2. Add new Media field in tab fields
3. Checkout to 'Form display' tab and hide Image field
4. Save new field to config file (drush cex -y)
5. Create 'post update' function
   - get all entity by type and if exist field image 
   - get fid in the Image field
   - check if exist media with current fid reference and get media id (mid)
   - if media doesn't exist create new media with current fid
   - set media to new field media
   - save entity
6. Edit your template / add new field media
7. Remove old field
8. Save configs
9. Run drush updb -y
10. Enjoy your results!